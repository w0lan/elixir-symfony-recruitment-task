defmodule PhoenixApiWeb.ErrorJSON do
  @moduledoc """
  This module is invoked by your endpoint in case of errors on JSON requests.

  See config/config.exs.
  """

  # If you want to customize a particular status code,
  # you may add your own clauses, such as:
  #
  # def render("500.json", _assigns) do
  #   %{errors: %{detail: "Internal Server Error"}}
  # end

  # By default, Phoenix returns the status message from
  # the template name. For example, "404.json" becomes
  # "Not Found".
  def render("404.json", _assigns) do
    %{error: %{code: "not_found", message: "Not Found", details: %{}}}
  end

  def render("400.json", _assigns) do
    %{error: %{code: "bad_request", message: "Bad Request", details: %{}}}
  end

  def render("422.json", _assigns) do
    %{error: %{code: "validation_error", message: "Unprocessable Content", details: %{}}}
  end

  def render("500.json", _assigns) do
    %{error: %{code: "internal_error", message: "Internal Server Error", details: %{}}}
  end

  def render(template, _assigns) do
    code =
      template
      |> String.replace_suffix(".json", "")
      |> then(fn status -> "http_" <> status end)

    %{error: %{code: code, message: Phoenix.Controller.status_message_from_template(template), details: %{}}}
  end
end
