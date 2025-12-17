defmodule PhoenixApiWeb.UserControllerTest do
  use PhoenixApiWeb.ConnCase

  import PhoenixApi.UsersFixtures

  @create_attrs %{
    first_name: "Jan",
    last_name: "Kowalski",
    birthdate: "1990-01-01",
    gender: "male"
  }
  @update_attrs %{
    first_name: "Adam",
    last_name: "Nowak",
    birthdate: "1995-05-05",
    gender: "male"
  }
  @invalid_attrs %{first_name: nil, last_name: nil}

  setup %{conn: conn} do
    {:ok, conn: put_req_header(conn, "accept", "application/json")}
  end

  describe "index" do
    test "lists all users", %{conn: conn} do
      conn = get(conn, ~p"/users")
      assert json_response(conn, 200)["data"] == []
      assert json_response(conn, 200)["meta"]["total"] == 0
    end

    test "lists users with pagination meta", %{conn: conn} do
      user_fixture()
      conn = get(conn, ~p"/users")
ni
      data = json_response(conn, 200)["data"]
      meta = json_response(conn, 200)["meta"]

      assert length(data) == 1
      assert meta["total"] == 1
      assert meta["page"] == 1
    end
    
    test "returns error for invalid query params", %{conn: conn} do
      conn = get(conn, ~p"/users?page=invalid")
      assert json_response(conn, 400)["error"]["code"] == "invalid_params"
    end
  end

  describe "create user" do
    test "renders user when data is valid", %{conn: conn} do
      conn = post(conn, ~p"/users", @create_attrs)
      assert %{"id" => id} = json_response(conn, 201)["data"]

      conn = get(conn, ~p"/users/#{id}")
      assert json_response(conn, 200)["data"]["first_name"] == "Jan"
    end

    test "renders errors when data is invalid", %{conn: conn} do
      conn = post(conn, ~p"/users", @invalid_attrs)
      assert json_response(conn, 422)["error"]["code"] == "validation_error"
    end
  end

  describe "update user" do
    setup [:create_user]

    test "renders user when data is valid", %{conn: conn, user: %{id: id} = user} do
      conn = put(conn, ~p"/users/#{user}", @update_attrs)
      assert %{"id" => ^id} = json_response(conn, 200)["data"]

      conn = get(conn, ~p"/users/#{id}")
      assert json_response(conn, 200)["data"]["first_name"] == "Adam"
    end

    test "renders errors when data is invalid", %{conn: conn, user: user} do
      conn = put(conn, ~p"/users/#{user}", @invalid_attrs)
      assert json_response(conn, 422)["error"]["code"] == "validation_error"
    end
  end

  describe "delete user" do
    setup [:create_user]

    test "deletes chosen user", %{conn: conn, user: user} do
      conn = delete(conn, ~p"/users/#{user}")
      assert response(conn, 204)

      conn = get(conn, ~p"/users/#{user}")
      assert json_response(conn, 404)["error"]["code"] == "not_found"
    end
  end

  defp create_user(_) do
    user = user_fixture()
    %{user: user}
  end
end

