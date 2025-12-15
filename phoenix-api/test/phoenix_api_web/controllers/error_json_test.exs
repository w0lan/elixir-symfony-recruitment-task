defmodule PhoenixApiWeb.ErrorJSONTest do
  use PhoenixApiWeb.ConnCase, async: true

  test "renders 404" do
    assert PhoenixApiWeb.ErrorJSON.render("404.json", %{}) ==
             %{error: %{code: "not_found", message: "Not Found", details: %{}}}
  end

  test "renders 500" do
    assert PhoenixApiWeb.ErrorJSON.render("500.json", %{}) ==
             %{error: %{code: "internal_error", message: "Internal Server Error", details: %{}}}
  end
end
