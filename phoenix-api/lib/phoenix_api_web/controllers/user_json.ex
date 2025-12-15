defmodule PhoenixApiWeb.UserJSON do
  alias PhoenixApi.Users.User

  def index(%{users: users, meta: meta}) do
    %{data: Enum.map(users, &data/1), meta: meta}
  end

  def show(%{user: user}) do
    %{data: data(user)}
  end

  def data(%User{} = user) do
    %{
      id: user.id,
      first_name: user.first_name,
      last_name: user.last_name,
      birthdate: encode_date(user.birthdate),
      gender: encode_gender(user.gender),
      inserted_at: encode_datetime(user.inserted_at),
      updated_at: encode_datetime(user.updated_at)
    }
  end

  defp encode_gender(nil), do: nil
  defp encode_gender(value) when is_atom(value), do: Atom.to_string(value)
  defp encode_gender(value) when is_binary(value), do: value

  defp encode_date(nil), do: nil
  defp encode_date(%Date{} = date), do: Date.to_iso8601(date)

  defp encode_datetime(nil), do: nil
  defp encode_datetime(%DateTime{} = dt), do: DateTime.to_iso8601(dt)
end

