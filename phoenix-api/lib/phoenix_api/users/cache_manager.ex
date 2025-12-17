defmodule PhoenixApi.Users.CacheManager do
  use GenServer
  require Logger

  # Client API

  def start_link(_opts) do
    GenServer.start_link(__MODULE__, %{}, name: __MODULE__)
  end


  # Server Callbacks

  @impl true
  def init(state) do
    # Subscribe to user events
    Phoenix.PubSub.subscribe(PhoenixApi.PubSub, "user_events")
    {:ok, state}
  end


  @impl true
  def handle_info(:warmup, state) do
    # Skip warmup in test environment to avoid DB connection issues
    if System.get_env("MIX_ENV") != "test" do
      Logger.debug("Performing cache warmup for default users list...")

      # Default params same as in UserParams
      default_opts = %{
        page: 1,
        page_size: 50,
        sort_by: :id,
        sort_dir: :asc
      }

      PhoenixApi.Users.list_users(default_opts)
    end

    {:noreply, state}
  end

  @impl true
  def handle_info({:user_changed, _user_id}, state) do
    Logger.debug("Received user change event, invalidating cache")
    :ets.delete_all_objects(:users_cache)
    send(self(), :warmup)
    {:noreply, state}
  end

  @impl true
  def handle_info(:users_bulk_imported, state) do
    Logger.debug("Received bulk import event, invalidating cache")
    :ets.delete_all_objects(:users_cache)
    send(self(), :warmup)
    {:noreply, state}
  end
end
