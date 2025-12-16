defmodule PhoenixApiWeb.UserParamsTest do
  use ExUnit.Case, async: true
  alias PhoenixApiWeb.UserParams

  describe "parse/1" do
    test "returns defaults when params are empty" do
      {:ok, opts} = UserParams.parse(%{})
      assert opts.page == 1
      assert opts.page_size == 50
      assert opts.sort_by == :id
      assert opts.sort_dir == :asc
      assert opts.first_name == nil
    end

    test "parses valid params" do
      params = %{
        "page" => "2",
        "page_size" => "10",
        "sort_by" => "last_name",
        "sort_dir" => "desc",
        "first_name" => "Jan",
        "birthdate_from" => "2000-01-01",
        "birthdate_to" => "2020-01-01",
        "gender" => "male"
      }

      {:ok, opts} = UserParams.parse(params)

      assert opts.page == 2
      assert opts.page_size == 10
      assert opts.sort_by == :last_name
      assert opts.sort_dir == :desc
      assert opts.first_name == "Jan"
      assert opts.birthdate_from == ~D[2000-01-01]
      assert opts.gender == :male
    end

    test "returns error for invalid integer" do
      {:error, {:invalid_params, details}} = UserParams.parse(%{"page" => "abc"})
      assert Map.has_key?(details, :page)
    end

    test "returns error for invalid date" do
      {:error, {:invalid_params, details}} = UserParams.parse(%{"birthdate_from" => "invalid"})
      assert Map.has_key?(details, :birthdate_from)
    end

    test "returns error for invalid birthdate range" do
      params = %{
        "birthdate_from" => "2020-01-01",
        "birthdate_to" => "2010-01-01" # to < from
      }
      {:error, {:invalid_params, details}} = UserParams.parse(params)
      assert Map.has_key?(details, :birthdate_range)
    end

    test "sanitizes strings (blank to nil)" do
      {:ok, opts} = UserParams.parse(%{"first_name" => ""})
      assert opts.first_name == nil
    end
  end
end

