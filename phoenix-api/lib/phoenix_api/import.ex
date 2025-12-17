defmodule PhoenixApi.Import do
  alias PhoenixApi.Repo
  alias PhoenixApi.Users.User
  alias PhoenixApi.Users.CacheManager

  @male_first_names_default_url "https://api.dane.gov.pl/resources/21495"
  @female_first_names_default_url "https://api.dane.gov.pl/resources/21489"
  @male_last_names_default_url "https://api.dane.gov.pl/resources/54097"
  @female_last_names_default_url "https://api.dane.gov.pl/resources/54098"

  @min_birthdate ~D[1970-01-01]
  @max_birthdate ~D[2024-12-31]

  def run do
    with {:ok, male_first_names} <- fetch_top_100_first_names(male_first_names_url()),
         {:ok, female_first_names} <- fetch_top_100_first_names(female_first_names_url()),
         {:ok, male_last_names} <- fetch_top_100_last_names(male_last_names_url()),
         {:ok, female_last_names} <- fetch_top_100_last_names(female_last_names_url()) do
      now = DateTime.utc_now() |> DateTime.truncate(:second)

      entries =
        Enum.map(1..100, fn _ ->
          gender = Enum.random([:male, :female])

          {first_name, last_name} =
            case gender do
              :male -> {Enum.random(male_first_names), Enum.random(male_last_names)}
              :female -> {Enum.random(female_first_names), Enum.random(female_last_names)}
            end

          %{
            first_name: first_name,
            last_name: last_name,
            birthdate: random_birthdate(@min_birthdate, @max_birthdate),
            gender: gender,
            inserted_at: now,
            updated_at: now
          }
        end)

      # We use insert_all for performance reasons.
      # Note that this bypasses changeset validations, but since we generate data internally,
      # we can ensure its validity in the map generation above.
      {inserted, _} = Repo.insert_all(User, entries)
      CacheManager.invalidate()
      {:ok, inserted}
    else
      {:error, {:fetch_failed, details}} -> {:error, {:fetch_failed, details}}
      {:error, {:parse_failed, details}} -> {:error, {:parse_failed, details}}
      {:error, details} -> {:error, details}
    end
  end

  defp male_first_names_url do
    System.get_env("PESEL_MALE_FIRST_NAMES_URL") |> blank_to_nil() || @male_first_names_default_url
  end

  defp female_first_names_url do
    System.get_env("PESEL_FEMALE_FIRST_NAMES_URL") |> blank_to_nil() || @female_first_names_default_url
  end

  defp male_last_names_url do
    System.get_env("PESEL_MALE_LAST_NAMES_URL") |> blank_to_nil() || @male_last_names_default_url
  end

  defp female_last_names_url do
    System.get_env("PESEL_FEMALE_LAST_NAMES_URL") |> blank_to_nil() || @female_last_names_default_url
  end

  defp blank_to_nil(nil), do: nil
  defp blank_to_nil(""), do: nil
  defp blank_to_nil(value) when is_binary(value), do: value

  defp fetch_top_100_first_names(url) do
    with {:ok, rows} <- fetch_rows(url),
         {:ok, {name_idx, count_idx}} <- detect_columns(rows, [:first_name]) do
      {:ok,
       rows
       |> rows_to_name_counts(name_idx, count_idx)
       |> Enum.sort_by(fn {_name, count} -> -count end)
       |> Enum.take(100)
       |> Enum.map(fn {name, _count} -> name end)}
    end
  end

  defp fetch_top_100_last_names(url) do
    with {:ok, rows} <- fetch_rows(url),
         {:ok, {name_idx, count_idx}} <- detect_columns(rows, [:last_name]) do
      {:ok,
       rows
       |> rows_to_name_counts(name_idx, count_idx)
       |> Enum.sort_by(fn {_name, count} -> -count end)
       |> Enum.take(100)
       |> Enum.map(fn {name, _count} -> name end)}
    end
  end

  defp fetch_rows(url) do
    case fetch_csv(url) do
      {:ok, csv} ->
        case parse_csv(csv) do
          {:ok, rows} -> {:ok, rows}
          {:error, details} -> {:error, {:parse_failed, Map.put(details, :url, url)}}
        end

      {:error, details} ->
        {:error, {:fetch_failed, Map.put(details, :url, url)}}
    end
  end

  defp fetch_csv(url) do
    with {:ok, %{status: status, headers: headers, body: body}} <-
           Req.get(url: url, headers: [{"accept", "text/csv,application/json;q=0.9,*/*;q=0.8"}]) do
      cond do
        status in 200..299 and looks_like_csv?(headers, body) ->
          {:ok, body}

        status in 200..299 ->
          with {:ok, next_url} <- extract_download_url(body),
               {:ok, %{status: status2, headers: headers2, body: body2}} <-
                 Req.get(url: next_url, headers: [{"accept", "text/csv,*/*"}]) do
            if status2 in 200..299 and looks_like_csv?(headers2, body2) do
              {:ok, body2}
            else
              {:error, %{status: status2}}
            end
          else
            {:error, details} -> {:error, details}
          end

        true ->
          {:error, %{status: status}}
      end
    else
      {:error, exception} -> {:error, %{error: Exception.message(exception)}}
    end
  end

  defp looks_like_csv?(headers, body) when is_binary(body) do
    headers = normalize_headers(headers)

    content_type =
      headers
      |> Enum.find_value("", fn {k, v} ->
        if k |> to_string() |> String.downcase() == "content-type", do: to_string(v), else: nil
      end)
      |> String.downcase()

    body_start = body |> String.trim_leading() |> String.slice(0, 1)

    cond do
      String.contains?(content_type, "application/json") -> false
      String.contains?(content_type, "text/html") -> false
      body_start in ["{", "<"] -> false
      String.contains?(content_type, "text/csv") -> true
      String.contains?(content_type, "application/csv") -> true
      String.contains?(content_type, "application/vnd.ms-excel") -> true
      String.contains?(content_type, "application/octet-stream") and String.contains?(body, "\n") -> true
      String.contains?(content_type, "text/plain") and String.contains?(body, "\n") -> true
      true -> false
    end
  end

  defp looks_like_csv?(_headers, _body), do: false

  defp normalize_headers(headers) when is_list(headers), do: headers

  defp normalize_headers(headers) when is_map(headers) do
    Enum.map(headers, fn {k, v} ->
      value =
        case v do
          [first | _] -> first
          other -> other
        end

      {k, value}
    end)
  end

  defp normalize_headers(_), do: []

  defp extract_download_url(body) when is_binary(body) do
    with {:ok, json} <- Jason.decode(body) do
      extract_download_url(json)
    else
      _ -> {:error, %{error: "no_download_url"}}
    end
  end

  defp extract_download_url(%{} = json) do
    url =
      case json do
        %{"data" => %{"attributes" => %{} = attrs}} ->
          attrs["csv_download_url"] ||
            attrs["csv_file_url"] ||
            find_csv_url_in_files(attrs["files"])

        %{} ->
          json["file"] || json["link"] || json["url"]

        _ ->
          nil
      end

    if is_binary(url) and String.starts_with?(url, "http") do
      {:ok, url}
    else
      {:error, %{error: "no_download_url"}}
    end
  end

  defp find_csv_url_in_files(files) when is_list(files) do
    case Enum.find(files, fn file -> is_map(file) and (file["format"] == "csv" or file[:format] == "csv") end) do
      %{"download_url" => url} -> url
      %{download_url: url} -> url
      _ -> nil
    end
  end

  defp find_csv_url_in_files(_), do: nil

  defp parse_csv(csv) when is_binary(csv) do
    csv = csv |> String.trim_leading("\uFEFF")
    lines = csv |> String.split(["\r\n", "\n"], trim: true)

    case lines do
      [] ->
        {:error, %{error: "empty_csv"}}

      [header_line | data_lines] ->
        delimiter = detect_delimiter(header_line)
        header = parse_line(header_line, delimiter)

        rows =
          data_lines
          |> Enum.map(&parse_line(&1, delimiter))
          |> Enum.reject(fn cols -> Enum.all?(cols, &(&1 == "")) end)

        {:ok, %{header: header, rows: rows}}
    end
  end

  defp detect_delimiter(line) do
    cond do
      String.contains?(line, ";") -> ";"
      String.contains?(line, ",") -> ","
      String.contains?(line, "\t") -> "\t"
      true -> ";"
    end
  end

  defp parse_line(line, delimiter) do
    line
    |> String.trim_trailing("\r")
    |> String.split(delimiter)
    |> Enum.map(fn value ->
      value
      |> String.trim()
      |> String.trim_leading("\"")
      |> String.trim_trailing("\"")
    end)
  end

  defp detect_columns(%{header: header, rows: rows}, kind) do
    name_patterns =
      case kind do
        [:first_name] -> ["imie", "imię", "name", "first"]
        [:last_name] -> ["nazwisko", "surname", "last"]
      end

    count_patterns = ["liczba", "count", "ile", "wystap", "wystąp"]

    header_norm = Enum.map(header, &normalize_header/1)

    name_idx =
      header_norm
      |> Enum.with_index()
      |> Enum.find_value(fn {h, idx} ->
        if Enum.any?(name_patterns, &String.contains?(h, &1)), do: idx, else: nil
      end) || 0

    count_idx =
      header_norm
      |> Enum.with_index()
      |> Enum.find_value(fn {h, idx} ->
        if Enum.any?(count_patterns, &String.contains?(h, &1)), do: idx, else: nil
      end)

    if rows == [] do
      {:error, %{error: "no_rows"}}
    else
      {:ok, {name_idx, count_idx}}
    end
  end

  defp normalize_header(value) do
    value |> String.downcase() |> String.replace(~r/[^a-z0-9ąćęłńóśźż]/u, "")
  end

  defp rows_to_name_counts(%{rows: rows}, name_idx, count_idx) do
    rows
    |> Enum.map(fn cols ->
      name = cols |> Enum.at(name_idx) |> blank_to_nil()

      count =
        case count_idx do
          nil -> 0
          idx -> cols |> Enum.at(idx) |> parse_int()
        end

      {name, count}
    end)
    |> Enum.reject(fn {name, _count} -> is_nil(name) end)
  end

  defp parse_int(nil), do: 0
  defp parse_int(""), do: 0

  defp parse_int(value) when is_binary(value) do
    value = value |> String.replace(~r/[^0-9]/, "")

    case Integer.parse(value) do
      {int, ""} -> int
      _ -> 0
    end
  end

  defp random_birthdate(%Date{} = min_date, %Date{} = max_date) do
    min = Date.to_gregorian_days(min_date)
    max = Date.to_gregorian_days(max_date)

    Date.from_gregorian_days(Enum.random(min..max))
  end
end

