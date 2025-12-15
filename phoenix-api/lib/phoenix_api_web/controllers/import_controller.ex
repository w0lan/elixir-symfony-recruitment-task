defmodule PhoenixApiWeb.ImportController do
  use PhoenixApiWeb, :controller

  alias PhoenixApi.Import
  alias PhoenixApiWeb.APIError

  def create(conn, _params) do
    case authorize(conn) do
      :ok ->
        case safe_import() do
          {:ok, inserted} ->
            json(conn, %{data: %{inserted: inserted}})

          {:error, {:fetch_failed, details}} ->
            APIError.send(conn, 502, "import_failed", "Import failed", Map.put(details, :stage, "fetch"))

          {:error, {:parse_failed, details}} ->
            APIError.send(conn, 502, "import_failed", "Import failed", Map.put(details, :stage, "parse"))

          {:error, {:unexpected, message}} ->
            APIError.send(conn, 500, "internal_error", "Internal Server Error", %{error: message})

          {:error, details} ->
            APIError.send(conn, 500, "internal_error", "Internal Server Error", %{details: details})
        end

      {:error, :unauthorized} ->
        APIError.send(conn, 401, "unauthorized", "Unauthorized", %{})
    end
  end

  defp authorize(conn) do
    token = System.get_env("IMPORT_TOKEN") |> blank_to_nil()

    if token do
      case get_req_header(conn, "authorization") do
        ["Bearer " <> provided] when provided == token -> :ok
        _ -> {:error, :unauthorized}
      end
    else
      :ok
    end
  end

  defp blank_to_nil(nil), do: nil
  defp blank_to_nil(""), do: nil
  defp blank_to_nil(value) when is_binary(value), do: value

  defp safe_import do
    try do
      Import.run()
    rescue
      exception ->
        {:error, {:unexpected, Exception.message(exception)}}
    end
  end
end

