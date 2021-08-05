# LynkCMS Connection Component

A simple library that helps manage PDO connections, assists in creating new connections and adds a few helper methods to the standard PDO interface.

## Classes

* Connection
* ConnectionPool
* ConnectionWrapped
* NewConnection

<hr />

## LynkCMS\Component\Connection\Connection

Connection class that extends the standard PDO class and adds a couple of helper methods to assist with querying the database. The constructor is the same as the inherited PDO object.

### Methods

**pdo()** - Returns `$this`. Only on the Connection object so that the Connection and ConnectionWrapped classes have the same interface.

- Return - `$this`.

**getDriver()** - Helper/alias to get the current connection's driver.

- Return - The current connection's driver.

**quote($string, $parameterType = PDO::PARAM_STR)** - Helper method to optionally quote an array of strings instead of looping through the PDO objects `quote` method manually.

- `$string` - The string or array of strings to be quoted.

- `$parameterType` - Provides a data type hint for drivers that have alternate quoting styles.

- Return - The quoted string or the quoted array of strings separated by a comma.

**run($query, $parameters = null, $deflayFetch = false)** - Helper method that queries the database and returns an array, the 'result set'. This method will also internally catch any error and return it as part of the result set.

- `$query` - The query to run or prepare and run.

- `$parameters` - The optional parameters for the query. If `null` is provided then a normal non-prepared query is ran. If an array is passed, even an empty on the query is prepared then ran.

- `$delayfetch` - Delays fetching the data and allows looping through the results directly from the statment provided in the result set.

## LynkCMS\Component\Connection\ConnectionPool

Class to manage named PDO connections. ConnectionPool will lazy-load the connections if they are provided as an array of configuration settings instead of an instantiated PDO object. A default connection can also be set by name so that the `get()` method's `$name` paramter is optional.

**__construct(Array $connections = Array(), $root = null, $default = 'default')** - The class's constructor.

- `$connections` - An array of connections (PDO or Connection or ConnectionWrapped) or a connection configuration which will be used to instantiate a connection when retrieved for the first time.

- `$root` - The root directory for SQLite connections that are provided via the configuration option. Optional, if the connection configuration's 'path' value starts with a / this is not used.

- `$default` - The name/key of the default connection. Defaults to 'default'.

**setDefault($default)** - Sets the default PDO connection by name.

- `$default` - The default PDO connections name.

**getDefault()** - Returns the default connection's name.

- Return - The default connection name.

**get($name = null)** - Get a registered connection.

- `$name` - The connection name/key, inf nothing is provided it returns teh default connection.

- Return - The connection object (Connection or ConnectionWrapped)

**set($name, $connection = null)** - Sets a connection by name. Accepts either a name and connection/connection configuration as the first and second parameters respectively or an array containing the names as keys and the connections/connection configurations as values.

- `$name` - The name of the connection.

- `$connection` - The connection or connection configuration as an array. This argument is optional if an array is provided with the names as the keys and the connections/configurations as the values.

**has($name = null)** - Check whether or not a connection exists by name. If null or nothing is provided this method checks if the default connection exists.

**remove($name)** - Removes a connection from the connection pool.

## LynkCMS\Component\Connection\ConnectionWrapped

Connection class that wraps a standard PDO class and adds a couple of helper methods to assist with querying the database. The constructor takes a PDO instance as the only argument. This class has the same interface as the `Connection` class with the an addional use of the magic `__call` method so that this class can be used interchangeably with the PDO class.

**__construct(PDO $pdo)** - The class contructor, takes a single PDO instance.

- `$pdo` - The PDO instance.

**__call($name, $args)** - Will call the method on the internal PDO object if it exists, otherwise an Exception is thrown.

- `$name` - The name of the method being called.

- `$args` - Arguments for the method being called.
