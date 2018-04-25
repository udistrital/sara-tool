#!/bin/bash -eu
if ! docker images | awk '{print $1}' | grep sara-tool &> /dev/null
then
docker build -t sara-tool .
fi
# https://github.com/moby/moby/issues/2838
COMMAND='php /root/codificadorBasicoSARA.php '$@
echo "Ejecutando: docker run -ti --rm sara-tool /bin/sh -c '$COMMAND'"
docker run -ti --rm sara-tool /bin/sh -c "$COMMAND"
