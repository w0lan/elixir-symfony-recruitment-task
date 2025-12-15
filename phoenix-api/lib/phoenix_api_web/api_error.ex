defmodule PhoenixApiWeb.APIError do
  import Plug.Conn
  import Phoenix.Controller, only: [json: 2]

  def send(conn, status, code, message, details \\ %{}) do
    conn
    |> put_status(status)
    |> json(%{error: %{code: code, message: message, details: details}})
  end
end

