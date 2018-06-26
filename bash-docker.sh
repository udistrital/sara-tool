#!/bin/bash -eu
if ! docker images | awk '{print $1}' | grep sara-tool &> /dev/null
then
  docker build -t sara-tool .
fi
# https://github.com/moby/moby/issues/2838
echo 'execute $ php /opt/sara-tool/codificadorBasicoSARA.php'
docker run --name sara-tool_bash --rm -v $PWD:/opt/sara-tool -i -t sara-tool bash
