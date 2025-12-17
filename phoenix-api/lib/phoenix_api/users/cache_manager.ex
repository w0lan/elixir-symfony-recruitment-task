defmodule PhoenixApi.Users.CacheManager do
  use GenServer
  require Logger

  # Client API

  def start_link(_opts) do
    GenServer.start_link(__MODULE__, %{}, name: __MODULE__)
  end

  @doc """
  Invalidates the entire users cache.
  """
  def invalidate do
    GenServer.cast(__MODULE__, :invalidate)
  end

  # Server Callbacks

  @impl true
  def init(state) do
    # Asynchronously trigger warmup after start
    send(self(), :warmup)
    {:ok, state}
  end

  @impl true
  def handle_cast(:invalidate, state) do
    Logger.debug("Invalidating users cache (ETS delete_all_objects)")
    :ets.delete_all_objects(:users_cache)
    {:noreply, state}
  end

  @impl true
  def handle_info(:warmup, state) do
    Logger.debug("Performing cache warmup for default users list...")

    # Default params same as in UserParams
    default_opts = %{
      page: 1,
      page_size: 50,
      sort_by: :id,
      sort_dir: :asc
    }

    PhoenixApi.Users.list_users(default_opts)

    {:noreply, state}
  end
end
