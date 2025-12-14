:inets.start()

port =
  System.get_env("PORT", "4000")
  |> String.to_integer()

docroot = Path.expand("www", __DIR__)

{:ok, _pid} =
  :inets.start(:httpd, [
    {:port, port},
    {:server_name, ~c"phoenix"},
    {:server_root, to_charlist(docroot)},
    {:document_root, to_charlist(docroot)},
    {:bind_address, {0, 0, 0, 0}},
    {:directory_index, [~c"index.html"]},
    {:mime_types, [
      {~c"html", ~c"text/html"},
      {~c"json", ~c"application/json"},
      {~c"txt", ~c"text/plain"}
    ]}
  ])

Process.sleep(:infinity)


