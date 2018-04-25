#!/bin/bash -eu
if ! docker images | awk '{print $1}' | grep sara-tool &> /dev/null
then
docker build -t sara-tool .
fi
# https://github.com/moby/moby/issues/2838
COMMAND='php /opt/sara-tool/codificadorBasicoSARA.php '$@
echo "Ejecutando: docker run -ti --rm -v $PWD:/opt/sara-tool sara-tool /bin/sh -c '$COMMAND'"
docker run -ti --rm -v $PWD:/opt/sara-tool sara-tool /bin/sh -c "$COMMAND"
