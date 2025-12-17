defmodule PhoenixApiWeb.TraceIdPlug do
  import Plug.Conn
  require Logger

  def init(opts), do: opts

  def call(conn, _opts) do
    trace_id =
      conn
      |> get_req_header("x-trace-id")
      |> List.first()
      |> case do
        nil -> generate_trace_id()
        existing -> existing
      end

    Logger.error("TraceIdPlug: received trace_id #{trace_id}")
    Logger.metadata(trace_id: trace_id)

    conn
    |> assign(:trace_id, trace_id)
    |> put_resp_header("x-trace-id", trace_id)
  end

  defp generate_trace_id do
    "trace-" <> (:crypto.strong_rand_bytes(8) |> Base.encode16(case: :lower))
  end
end
