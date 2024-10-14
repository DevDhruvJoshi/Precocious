<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credential Class Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-left: 4px solid #ccc;
            overflow-x: auto;
        }
        code {
            font-family: monospace;
            background: #eef;
            padding: 2px 4px;
        }
    </style>
</head>
<body>

<h1>Credential Class Documentation</h1>

<h2>Overview</h2>
<p>
    The <code>Credential</code> class is responsible for retrieving database configuration details based on various contexts, including default settings, owner settings, and tenant-specific settings. It utilizes environment variables and tenant details to generate the appropriate database configurations.
</p>

<h2>Constants</h2>

<h3><code>DB_KEYS</code></h3>
<pre><code>private const DB_KEYS = [
    'DB_Type',
    'DB_Host',
    'DB_Name',
    'DB_User',
    'DB_Password',
];</code></pre>
<p>
    An array of keys that define the required database configuration parameters.
</p>

<h2>Methods</h2>

<h3><code>getDBConfig(array $data): array</code></h3>
<p>
    <strong>Description</strong>: This method takes an associative array as input and extracts database configuration parameters based on the keys defined in <code>DB_KEYS</code>. If a key is not present in the input array, it defaults to an empty string.
</p>
<ul>
    <li><strong>Parameters</strong>:
        <ul>
            <li><code>array $data</code>: An associative array containing database configuration values.</li>
        </ul>
    </li>
    <li><strong>Returns</strong>: An array with database configuration values, ensuring all keys from <code>DB_KEYS</code> are present.</li>
</ul>

<h3><code>DefaultDB(): array</code></h3>
<p>
    <strong>Description</strong>: Retrieves the default database configuration by fetching environment variables.
</p>
<ul>
    <li><strong>Returns</strong>: An associative array containing the default database configuration values:
        <ul>
            <li><code>DB_Type</code></li>
            <li><code>DB_Host</code></li>
            <li><code>DB_Name</code></li>
            <li><code>DB_User</code></li>
            <li><code>DB_Password</code></li>
        </ul>
    </li>
</ul>

<h3><code>OwnerDB(): array</code></h3>
<p>
    <strong>Description</strong>: Retrieves the database configuration for the owner by fetching environment variables specific to owner settings.
</p>
<ul>
    <li><strong>Returns</strong>: An associative array containing the owner database configuration values:
        <ul>
            <li><code>OwnerDB_Type</code></li>
            <li><code>OwnerDB_Host</code></li>
            <li><code>OwnerDB_Name</code></li>
            <li><code>OwnerDB_User</code></li>
            <li><code>OwnerDB_Password</code></li>
        </ul>
    </li>
</ul>

<h3><code>TenantDB($SubDomain = null): array</code></h3>
<p>
    <strong>Description</strong>: Retrieves the database configuration for a tenant based on the provided subdomain. If no subdomain is provided, the method will look for details using a default mechanism.
</p>
<ul>
    <li><strong>Parameters</strong>:
        <ul>
            <li><code>string|null $SubDomain</code>: The subdomain of the tenant. If not provided, it attempts to derive it from the default mechanism.</li>
        </ul>
    </li>
    <li><strong>Returns</strong>: An associative array containing the tenant's database configuration values.</li>
</ul>

<h3><code>DB($SubDomain = null): array</code></h3>
<p>
    <strong>Description</strong>: This method determines which database configuration to return based on tenant permissions and subdomain. It prioritizes the owner database configuration if the subdomain matches the owner’s subdomain. Otherwise, it retrieves the tenant’s configuration or defaults to the default database configuration.
</p>
<ul>
    <li><strong>Parameters</strong>:
        <ul>
            <li><code>string|null $SubDomain</code>: The subdomain to check against. If not provided, it will use a default method to derive it.</li>
        </ul>
    </li>
    <li><strong>Returns</strong>: An associative array containing the relevant database configuration based on the logic described.</li>
</ul>

<h2>Usage Example</h2>
<pre><code>
// To get the default database configuration
$defaultConfig = Credential::DefaultDB();

// To get the owner database configuration
$ownerConfig = Credential::OwnerDB();

// To get tenant-specific configuration for a given subdomain
$tenantConfig = Credential::TenantDB('example_subdomain');

// To determine the appropriate database configuration based on permissions
$dbConfig = Credential::DB('example_subdomain');</code></pre>

<h2>Dependencies</h2>
<ul>
    <li>The class depends on the <code>Tenant</code> class, which provides methods to retrieve tenant details and permissions.</li>
    <li>It also relies on an <code>env</code> function to fetch environment variables, which should be defined elsewhere in the application.</li>
</ul>

</body>
</html>
