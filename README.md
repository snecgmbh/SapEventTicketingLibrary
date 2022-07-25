# SAP EventTicketing Library

This PHP lib should help use the SAP ET APIs (primary the XMLRPC) in several projects.

Usage:

```
$et = new SAP_ET("ticketing123.cld.ondemand.com");
$et->login("firma","user","password");
$customer = $et->tickets_get_customer(["customerid" => "TWER333-1"]);
```