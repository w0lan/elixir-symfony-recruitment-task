defmodule PhoenixApiWeb.UserController do
  use PhoenixApiWeb, :controller
  require Logger

  alias PhoenixApi.Users
  alias PhoenixApiWeb.APIError
  alias PhoenixApiWeb.UserParams

  def index(conn, params) do
    Logger.info("Listing users [#{conn.assigns.trace_id}]")

    with {:ok, opts} <- UserParams.parse(params),
         {:ok, users, meta} <- Users.list_users(opts) do
      render(conn, :index, users: users, meta: meta)
    else
      {:error, {:invalid_params, details}} ->
        APIError.send(conn, 400, "invalid_params", "Invalid query params", details)
    end
  end

  def show(conn, %{"id" => id_param}) do
    with {:ok, id} <- parse_id(id_param),
         {:ok, user} <- Users.get_user(id) do
      render(conn, :show, user: user)
    else
      {:error, {:invalid_params, details}} ->
        APIError.send(conn, 400, "invalid_params", "Invalid path params", details)

      {:error, :not_found} ->
        APIError.send(conn, 404, "not_found", "User not found", %{})
    end
  end

  def create(conn, params) do
    case Users.create_user(params) do
      {:ok, user} ->
        conn
        |> put_status(:created)
        |> render(:show, user: user)

      {:error, %Ecto.Changeset{} = changeset} ->
        APIError.send(conn, 422, "validation_error", "Validation error", changeset_details(changeset))
    end
  end

  def update(conn, %{"id" => id_param} = params) do
    attrs = Map.delete(params, "id")

    with {:ok, id} <- parse_id(id_param),
         {:ok, user} <- Users.get_user(id) do
      case Users.update_user(user, attrs) do
        {:ok, user} ->
          render(conn, :show, user: user)

        {:error, %Ecto.Changeset{} = changeset} ->
          APIError.send(conn, 422, "validation_error", "Validation error", changeset_details(changeset))
      end
    else
      {:error, {:invalid_params, details}} ->
        APIError.send(conn, 400, "invalid_params", "Invalid path params", details)

      {:error, :not_found} ->
        APIError.send(conn, 404, "not_found", "User not found", %{})
    end
  end

  def delete(conn, %{"id" => id_param}) do
    with {:ok, id} <- parse_id(id_param),
         {:ok, user} <- Users.get_user(id),
         {:ok, _} <- Users.delete_user(user) do
      send_resp(conn, :no_content, "")
    else
      {:error, {:invalid_params, details}} ->
        APIError.send(conn, 400, "invalid_params", "Invalid path params", details)

      {:error, :not_found} ->
        APIError.send(conn, 404, "not_found", "User not found", %{})
    end
  end

  defp parse_id(value) when is_binary(value) do
    case Integer.parse(value) do
      {id, ""} when id > 0 -> {:ok, id}
      _ -> {:error, {:invalid_params, %{id: value}}}
    end
  end

  defp changeset_details(changeset) do
    Ecto.Changeset.traverse_errors(changeset, fn {msg, opts} ->
      Enum.reduce(opts, msg, fn {key, value}, acc ->
        String.replace(acc, "%{#{key}}", to_string(value))
      end)
    end)
  end
end

