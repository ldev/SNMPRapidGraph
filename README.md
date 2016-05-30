# SNMPRapidGraph
## Reqirements
* PHP 5.4+ or something like that
* Postgres DB
* PHP-Postgres bindings
* PHP snmp tools


## Create DB user and table
Create the necessary postgres foobar. Replace the password with something a bit more safe.
```
sudo su - postgres
psql snmprapidgraph
CREATE USER snmprapidgraph WITH PASSWORD 'asdasdsad';
CREATE DATABASE snmprapidgraph;
GRANT ALL PRIVILEGES ON DATABASE "snmprapidgraph" to snmprapidgraph;
\q
```


```
CREATE TABLE data (
    id bigserial PRIMARY KEY,
    graph varchar (50) NOT NULL,
    direction varchar (50) NOT NULL check (direction in ('inbound', 'outbound')),
    time bigint NOT NULL,
    bps bigint NOT NULL,
    raw_readout bigint NOT NULL
);
```

## Rename db_connect_example.php to db_connect.php and fill in the DB user/password

## To run (lolmode):
  watch sudo -u www-data php -f /var/www/ldev.no/www_root/snmprapidgraph/www_root/fetch_snmp_data.php

