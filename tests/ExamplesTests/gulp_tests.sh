#!/bin/bash

WD="$(pwd)/$(dirname $1)"
TEST_DIR="${WD}/../../examples/gulp"
source "${WD}/lib.sh"

testComposer() {
    assertSuccess "composer update --no-interaction --prefer-dist ${composerArgs}"
}

testNpm() {
    assertSuccess "npm install -s -q"
}

testGulpClean() {
    assertSuccess "gulp clean"
    assertTrue "Test if dist/js dir not exists."   "[ ! -d \"www/dist/js\"  ]"
    assertTrue "Test if dist/css dir not exists."  "[ ! -d \"www/dist/css\" ]"
    assertTrue "Test if manifest.json not exists." "[ ! -f \"www/dist/manifest.json\" ]"
}

testGulpStyles() {
    gulp clean > /dev/null
    assertSuccess "gulp styles"

    assertTrue "Test if dist/js dir not exists."   "[ ! -d \"www/dist/js\"  ]"
    assertTrue "Test if dist/css dir exists."      "[ -d   \"www/dist/css\" ]"
    assertTrue "Test if manifest.json exists."     "[ -f   \"www/dist/manifest.json\" ]"

    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/css/app.css\" | test(\"dist/css/app[.](.){10}[.]css\")"'

    cssPath=`eval "cat www/dist/manifest.json | jq -r '.\"dist/css/app.css\"'"`
    assertTrue "Test if CSS file exists."  "[ -f 'www/${cssPath}' ]"

    cssMapPath="${cssPath/.css/.css.map}"
    assertTrue "Test if CSS MAP file exists."  "[ -f 'www/${cssMapPath}' ]"
}

testGulpScripts() {
    gulp clean > /dev/null
    assertSuccess "gulp scripts"

    assertTrue "Test if dist/js dir exists."       "[ -d   \"www/dist/js\"  ]"
    assertTrue "Test if dist/css dir not exists."  "[ ! -d \"www/dist/css\" ]"
    assertTrue "Test if manifest.json exists."     "[ -f   \"www/dist/manifest.json\" ]"

    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/js/app.js\"  | test(\"dist/js/app[.](.){10}[.]js\")"'

    jsPath=`eval  "cat www/dist/manifest.json | jq -r '.\"dist/js/app.js\"'"`
    assertTrue "Test if JS file exists."  "[ -f 'www/${jsPath}' ]"

    jsMapPath="${jsPath/.js/.js.map}"
    assertTrue "Test if JS MAP file exists."   "[ -f 'www/${jsMapPath}'  ]"
}

testGulpDefault() {
    gulp clean > /dev/null
    assertSuccess "gulp"

    assertTrue "Test if dist/js dir exists."       "[ -d \"www/dist/js\"  ]"
    assertTrue "Test if dist/css dir exists."      "[ -d \"www/dist/css\" ]"
    assertTrue "Test if manifest.json exists."     "[ -f \"www/dist/manifest.json\" ]"

    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/css/app.css\" | test(\"dist/css/app[.](.){10}[.]css\")"'
    assertOutputEquals 'true' 'cat www/dist/manifest.json | jq ".\"dist/js/app.js\"   | test(\"dist/js/app[.](.){10}[.]js\")"'

    jsPath=$(cat www/dist/manifest.json | jq -r '."dist/js/app.js"')
    cssPath=$(cat www/dist/manifest.json | jq -r '."dist/css/app.css"')
    assertTrue "Test if JS file exists."       "[ -f 'www/${jsPath}'  ]"
    assertTrue "Test if CSS file exists."      "[ -f 'www/${cssPath}' ]"

    jsMapPath="${jsPath/.js/.js.map}"
    cssMapPath="${cssPath/.css/.css.map}"
    assertTrue "Test if JS MAP file exists."   "[ -f 'www/${jsMapPath}'  ]"
    assertTrue "Test if CSS MAP file exists."  "[ -f 'www/${cssMapPath}' ]"
}

testRender() {
    gulp clean > /dev/null
    assertSuccess "gulp"

    jsPath=$(cat www/dist/manifest.json | jq -r '."dist/js/app.js"')
    cssPath=$(cat www/dist/manifest.json | jq -r '."dist/css/app.css"')

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
