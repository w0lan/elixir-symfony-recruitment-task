defmodule PhoenixApiWeb.UserParams do
  @default_page 1
  @default_page_size 50
  @max_page_size 100

  def parse(params) when is_map(params) do
    with {:ok, page} <- parse_int(params["page"], :page, @default_page, 1, nil),
         {:ok, page_size} <- parse_int(params["page_size"], :page_size, @default_page_size, 1, @max_page_size),
         {:ok, sort_by} <- parse_sort_by(params["sort_by"]),
         {:ok, sort_dir} <- parse_sort_dir(params["sort_dir"]),
         {:ok, birthdate_from} <- parse_date(params["birthdate_from"], :birthdate_from),
         {:ok, birthdate_to} <- parse_date(params["birthdate_to"], :birthdate_to),
         {:ok, gender} <- parse_gender(params["gender"]),
         :ok <- validate_birthdate_range(birthdate_from, birthdate_to) do
      {:ok,
       %{
         page: page,
         page_size: page_size,
         sort_by: sort_by,
         sort_dir: sort_dir,
         first_name: blank_to_nil(params["first_name"]),
         last_name: blank_to_nil(params["last_name"]),
         gender: gender,
         birthdate_from: birthdate_from,
         birthdate_to: birthdate_to
       }}
    else
      {:error, details} -> {:error, {:invalid_params, details}}
    end
  end

  defp blank_to_nil(nil), do: nil
  defp blank_to_nil(""), do: nil
  defp blank_to_nil(value) when is_binary(value), do: value

  defp parse_int(nil, _key, default, _min, _max), do: {:ok, default}
  defp parse_int("", _key, default, _min, _max), do: {:ok, default}

  defp parse_int(value, key, _default, min, max) when is_binary(value) do
    case Integer.parse(value) do
      {int, ""} -> validate_int_range(int, key, min, max)
      _ -> {:error, %{key => value}}
    end
  end

  defp validate_int_range(int, key, min, nil) when is_integer(int) do
    if int >= min, do: {:ok, int}, else: {:error, %{key => int}}
  end

  defp validate_int_range(int, key, min, max) when is_integer(int) do
    if int >= min and int <= max, do: {:ok, int}, else: {:error, %{key => int}}
  end

  defp parse_sort_by(nil), do: {:ok, :id}
  defp parse_sort_by(""), do: {:ok, :id}

  defp parse_sort_by(value) when is_binary(value) do
    fields = %{
      "id" => :id,
      "first_name" => :first_name,
      "last_name" => :last_name,
      "birthdate" => :birthdate,
      "gender" => :gender,
      "inserted_at" => :inserted_at,
      "updated_at" => :updated_at
    }

    case Map.fetch(fields, value) do
      {:ok, field} -> {:ok, field}
      :error -> {:error, %{sort_by: value}}
    end
  end

  defp parse_sort_dir(nil), do: {:ok, :asc}
  defp parse_sort_dir(""), do: {:ok, :asc}

  defp parse_sort_dir(value) when is_binary(value) do
    case value do
      "asc" -> {:ok, :asc}
      "desc" -> {:ok, :desc}
      _ -> {:error, %{sort_dir: value}}
    end
  end

  defp parse_date(nil, _key), do: {:ok, nil}
  defp parse_date("", _key), do: {:ok, nil}

  defp parse_date(value, key) when is_binary(value) do
    case Date.from_iso8601(value) do
      {:ok, date} -> {:ok, date}
      {:error, _} -> {:error, %{key => value}}
    end
  end

  defp validate_birthdate_range(nil, _), do: :ok
  defp validate_birthdate_range(_, nil), do: :ok

  defp validate_birthdate_range(from, to) do
    if Date.compare(from, to) in [:lt, :eq] do
      :ok
    else
      {:error, %{birthdate_range: %{birthdate_from: Date.to_iso8601(from), birthdate_to: Date.to_iso8601(to)}}}
    end
  end

  defp parse_gender(nil), do: {:ok, nil}
  defp parse_gender(""), do: {:ok, nil}

  defp parse_gender(value) when is_binary(value) do
    case value do
      "male" -> {:ok, :male}
      "female" -> {:ok, :female}
      _ -> {:error, %{gender: value}}
    end
  end
end

