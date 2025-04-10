#!/bin/bash

# Vent til Graylog er oppe
echo "⏳ Venter på Graylog..."
until curl -s http://graylog:9000/api/system/inputs -u admin:admin | grep inputs > /dev/null; do
  sleep 5
done

# Opprett GELF UDP input hvis den ikke finnes
echo "🚀 Oppretter GELF UDP input..."
curl -X POST http://graylog:9000/api/system/inputs -u admin:admin \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "title": "GELF UDP",
    "type": "org.graylog2.inputs.gelf.udp.GELFUDPInput",
    "configuration": {
      "bind_address": "0.0.0.0",
      "port": 12201,
      "recv_buffer_size": 262144,
      "decompress_size_limit": 8388608,
      "override_source": null,
      "force_rdns": false,
      "allow_override_date": true,
      "expand_structured_data": false,
      "store_full_message": false,
      "use_null_delimiter": true
    },
    "global": true,
    "node": null
  }'
