defmodule PhoenixApi.UsersTest do
  use PhoenixApi.DataCase

  alias PhoenixApi.Users
  alias PhoenixApi.Users.User

  describe "users" do
    @valid_attrs %{
      first_name: "Jan",
      last_name: "Kowalski",
      birthdate: ~D[1990-01-01],
      gender: :male
    }
    @update_attrs %{
      first_name: "Adam",
      last_name: "Nowak",
      birthdate: ~D[1995-05-05],
      gender: :male
    }
    @invalid_attrs %{first_name: nil, last_name: nil, birthdate: nil, gender: nil}

    def user_fixture(attrs \\ %{}) do
      {:ok, user} =
        attrs
        |> Enum.into(@valid_attrs)
        |> Users.create_user()

      user
    end

    test "list_users/1 returns all users by default" do
      user = user_fixture()
      {:ok, users, meta} = Users.list_users(%{page: 1, page_size: 20, sort_by: :id, sort_dir: :asc})
      assert users == [user]
      assert meta.total == 1
    end

    test "list_users/1 filters by first_name" do
      user1 = user_fixture(%{first_name: "UniqueName"})
      user2 = user_fixture(%{first_name: "OtherName"})

      {:ok, users, meta} = Users.list_users(%{
        page: 1, page_size: 20, sort_by: :id, sort_dir: :asc,
        first_name: "unique"
      })

      assert length(users) == 1
      assert hd(users).id == user1.id
      assert meta.total == 1
    end

    test "list_users/1 filters by gender" do
      male = user_fixture(%{gender: :male})
      female = user_fixture(%{gender: :female})

      {:ok, users, _} = Users.list_users(%{
        page: 1, page_size: 20, sort_by: :id, sort_dir: :asc,
        gender: :female
      })

      assert length(users) == 1
      assert hd(users).id == female.id
    end

    test "list_users/1 filters by birthdate range" do
      user1 = user_fixture(%{birthdate: ~D[1990-01-01]})
      user2 = user_fixture(%{birthdate: ~D[2000-01-01]})

      {:ok, users, _} = Users.list_users(%{
        page: 1, page_size: 20, sort_by: :id, sort_dir: :asc,
        birthdate_from: ~D[1995-01-01],
        birthdate_to: ~D[2005-01-01]
      })

      assert length(users) == 1
      assert hd(users).id == user2.id
    end

    test "list_users/1 sorts users" do
      user1 = user_fixture(%{first_name: "A", birthdate: ~D[1990-01-01]})
      user2 = user_fixture(%{first_name: "B", birthdate: ~D[2000-01-01]})

      # Descending by first_name
      {:ok, users, _} = Users.list_users(%{
        page: 1, page_size: 20, sort_by: :first_name, sort_dir: :desc
      })

      assert users == [user2, user1]

      # Ascending by birthdate
      {:ok, users, _} = Users.list_users(%{
        page: 1, page_size: 20, sort_by: :birthdate, sort_dir: :asc
      })

      assert users == [user1, user2]
    end

    test "list_users/1 paginates users" do
      for i <- 1..5, do: user_fixture(%{last_name: "User#{i}"})

      {:ok, users, meta} = Users.list_users(%{
        page: 1, page_size: 2, sort_by: :id, sort_dir: :asc
      })

      assert length(users) == 2
      assert meta.total == 5
      assert meta.page == 1

      {:ok, users_page2, meta} = Users.list_users(%{
        page: 2, page_size: 2, sort_by: :id, sort_dir: :asc
      })

      assert length(users_page2) == 2
      assert meta.page == 2
    end

    test "get_user/1 returns the user with given id" do
      user = user_fixture()
      assert {:ok, %User{} = returned_user} = Users.get_user(user.id)
      assert returned_user.id == user.id
    end

    test "create_user/1 with valid data creates a user" do
      assert {:ok, %User{} = user} = Users.create_user(@valid_attrs)
      assert user.first_name == "Jan"
      assert user.last_name == "Kowalski"
      assert user.gender == :male
    end

    test "create_user/1 with invalid data returns error changeset" do
      assert {:error, %Ecto.Changeset{}} = Users.create_user(@invalid_attrs)
    end

    test "update_user/2 with valid data updates the user" do
      user = user_fixture()
      assert {:ok, %User{} = updated_user} = Users.update_user(user, @update_attrs)
      assert updated_user.first_name == "Adam"
      assert updated_user.last_name == "Nowak"
    end

    test "update_user/2 with invalid data returns error changeset" do
      user = user_fixture()
      assert {:error, %Ecto.Changeset{}} = Users.update_user(user, @invalid_attrs)
      assert {:ok, %User{}} = Users.get_user(user.id)
    end

    test "delete_user/1 deletes the user" do
      user = user_fixture()
      assert {:ok, %User{}} = Users.delete_user(user)
      assert {:error, :not_found} = Users.get_user(user.id)
    end
  end
end

