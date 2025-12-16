defmodule PhoenixApi.Users.User do
  use Ecto.Schema

  import Ecto.Changeset

  schema "users" do
    field :first_name, :string
    field :last_name, :string
    field :birthdate, :date
    field :gender, Ecto.Enum, values: [:male, :female]

    timestamps(type: :utc_datetime)
  end

  def changeset(user, attrs) do
    user
    |> cast(attrs, [:first_name, :last_name, :birthdate, :gender])
    |> validate_required([:first_name, :last_name, :birthdate, :gender])
    |> validate_length(:first_name, max: 255)
    |> validate_length(:last_name, max: 255)
  end
end


