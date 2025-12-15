defmodule PhoenixApi.Users do
  import Ecto.Query, warn: false

  alias PhoenixApi.Repo
  alias PhoenixApi.Users.User

  def list_users(params) when is_map(params) do
    with {:ok, opts} <- parse_list_opts(params) do
      filtered_query = users_query(opts)

      total = Repo.aggregate(filtered_query, :count, :id)

      users =
        filtered_query
        |> apply_sort(opts)
        |> apply_pagination(opts)
        |> Repo.all()

      meta = %{page: opts.page, page_size: opts.page_size, total: total}

      {:ok, users, meta}
    end
  end

  def get_user(id) when is_integer(id) do
    case Repo.get(User, id) do
      nil -> {:error, :not_found}
      user -> {:ok, user}
    end
  end

  def create_user(attrs) when is_map(attrs) do
    %User{}
    |> User.changeset(attrs)
    |> Repo.insert()
  end

  def update_user(%User{} = user, attrs) when is_map(attrs) do
    user
    |> User.changeset(attrs)
    |> Repo.update()
  end

  def delete_user(%User{} = user) do
    Repo.delete(user)
  end

  defp users_query(opts) do
    from(u in User)
    |> maybe_filter_first_name(opts.first_name)
    |> maybe_filter_last_name(opts.last_name)
    |> maybe_filter_gender(opts.gender)
    |> maybe_filter_birthdate_from(opts.birthdate_from)
    |> maybe_filter_birthdate_to(opts.birthdate_to)
  end

  defp maybe_filter_first_name(query, nil), do: query

  defp maybe_filter_first_name(query, first_name) do
    where(query, [u], ilike(u.first_name, ^"%#{first_name}%"))
  end

  defp maybe_filter_last_name(query, nil), do: query

  defp maybe_filter_last_name(query, last_name) do
    where(query, [u], ilike(u.last_name, ^"%#{last_name}%"))
  end

  defp maybe_filter_gender(query, nil), do: query

  defp maybe_filter_gender(query, gender) do
    where(query, [u], u.gender == ^gender)
  end

  defp maybe_filter_birthdate_from(query, nil), do: query

  defp maybe_filter_birthdate_from(query, birthdate_from) do
    where(query, [u], u.birthdate >= ^birthdate_from)
  end

  defp maybe_filter_birthdate_to(query, nil), do: query

  defp maybe_filter_birthdate_to(query, birthdate_to) do
    where(query, [u], u.birthdate <= ^birthdate_to)
  end

  defp apply_sort(query, %{sort_by: sort_by, sort_dir: sort_dir}) do
    order_by(query, [u], [{^sort_dir, field(u, ^sort_by)}])
  end

  defp apply_pagination(query, %{page: page, page_size: page_size}) do
    offset = (page - 1) * page_size
    query |> limit(^page_size) |> offset(^offset)
  end

  defp parse_list_opts(params) do
    with {:ok, page} <- parse_int(params["page"], :page, 1, 1, nil),
         {:ok, page_size} <- parse_int(params["page_size"], :page_size, 20, 1, 100),
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

