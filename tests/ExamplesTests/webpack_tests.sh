#!/bin/bash

WD="$(pwd)/$(dirname $1)"
TEST_DIR="${WD}/../../examples/webpack"
source "${WD}/lib.sh"

testComposer() {
    assertSuccess "composer update --no-interaction --prefer-dist ${composerArgs}"
}

testNpm() {
    assertSuccess "npm install -s -q"
}

testWebpack() {
    assertSuccess "webpack"

    assertTrue "Test if dist dir exists."       "[ -d \"www/dist\"  ]"
    assertTrue "Test if manifest.json exists."  "[ -f \"www/dist/manifest.json\" ]"

    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/app.css\" | test(\"dist/app[.](.){8}[.]css\")"'
    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/app.js\"  | test(\"dist/app[.](.){8}[.]js\")"'

    jsPath=$(cat www/dist/manifest.json | jq -r  '."dist/app.js"')
    cssPath=$(cat www/dist/manifest.json | jq -r '."dist/app.css"')
    assertTrue "Test if JS file exists."       "[ -f 'www/${jsPath}'  ]"
    assertTrue "Test if CSS file exists."      "[ -f 'www/${cssPath}' ]"

    jsMapPath="${jsPath/.js/.js.map}"
    cssMapPath="${cssPath/.css/.css.map}"
    assertTrue "Test if JS MAP file exists."   "[ -f 'www/${jsMapPath}'  ]"
    assertTrue "Test if CSS MAP file exists."  "[ -f 'www/${cssMapPath}' ]"

    export REQUEST_METHOD=GET
    export CONTENT_TYPE=text/html
    export SCRIPT_FILENAME=www/index.php
    export SCRIPT_NAME=/index.php
    export PATH_INFO=/
    export REQUEST_URI=/
    export SERVER_NAME=site.tld
    export SERVER_PROTOCOL=HTTP/1.1
    export HTTP_HOST=site.tld
    export REDIRECT_STATUS=CGI

    assertOutputContains "/${jsPath}"  "php-cgi"
    assertOutputContains "/${cssPath}" "php-cgi"
}
