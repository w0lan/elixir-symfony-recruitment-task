defmodule PhoenixApi.UsersFixtures do
  @moduledoc """
  This module defines test helpers for creating
  entities via the `PhoenixApi.Users` context.
  """

  @doc """
  Generate a user.
  """
  def user_fixture(attrs \\ %{}) do
    {:ok, user} =
      attrs
      |> Enum.into(%{
        birthdate: ~D[1990-01-01],
        first_name: "Jan",
        gender: :male,
        last_name: "Kowalski"
      })
      |> PhoenixApi.Users.create_user()

    user
  end
end

