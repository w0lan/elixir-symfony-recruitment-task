defmodule PhoenixApi.Users do
  import Ecto.Query, warn: false

  alias PhoenixApi.Repo
  alias PhoenixApi.Users.User

  def list_users(opts) when is_map(opts) do
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
    |> maybe_filter_first_name(Map.get(opts, :first_name))
    |> maybe_filter_last_name(Map.get(opts, :last_name))
    |> maybe_filter_gender(Map.get(opts, :gender))
    |> maybe_filter_birthdate_from(Map.get(opts, :birthdate_from))
    |> maybe_filter_birthdate_to(Map.get(opts, :birthdate_to))
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
end
