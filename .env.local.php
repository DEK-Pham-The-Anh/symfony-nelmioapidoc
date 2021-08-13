<?php

$allow_origins = [
  'https://my-domain1.com', 
  'https://my-domain2.com'
];
$allow_origins = "[" . implode(', ', $allow_origins) . "]";

return array (
  'APP_ENV' => 'prod',
  'APP_DEBUG' => 0,
  'APP_SECRET' => 'dfgs2452424524fhgfh45hj55jhghj56',
  'CORS_ALLOW_ORIGIN' => '^https://(my-domain1.com|my-domain2.com)$',
  'ALLOW_ORIGINS' => $allow_origins
);
