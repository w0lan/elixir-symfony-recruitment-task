defmodule PhoenixApiWeb.HealthController do
  use PhoenixApiWeb, :controller

  def show(conn, _params) do
    json(conn, %{status: "ok"})
  end
end


