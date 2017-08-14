#!/bin/bash

setUp() {
    # Change dir to example
    cd "${TEST_DIR}"
}

assertSuccess() {
    echo "RUNNING: $@"
    eval "$@"
    code=$?

    echo "EXIT CODE: $code"
    echo

    assertEquals "Command '$@' failed. Return code" 0 $code
}

assertOutputEquals() {
    echo "RUNNING: ${2:0:30}"
    output=`eval "$2"`
    output=${output/\n/}
    code=$?

    echo "OUTPUT: ${output:0:30}"
    echo "EXIT CODE: ${code}"
    echo

    assertEquals "Command '$2' failed. Return code" 0 $?
    assertEquals "Command '$2' invalid output." "$1" "$output"
}

assertOutputContains() {
    echo "RUNNING: ${2:0:30}"
    output=`eval "$2"`
    output=${output/\n/}
    code=$?

    echo "OUTPUT: ${output:0:30}"
    echo "EXIT CODE: ${code}"
    echo

    assertEquals "Command '$2' failed. Return code" 0 $?

    # Test if contains output
    echo "$output" | grep "$1" > /dev/null
    if [ $? -ne 0 ]; then
        echo -e "FULL OUTPUT: ${output}\n\n"
        fail "Command '$2' output not contains '$1'."
    fi
}
