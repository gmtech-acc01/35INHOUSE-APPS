

FOR /L %%A IN (1,1,10000000) DO (
  sleep 10
  php BulkSMSBatchProcessor.php

)