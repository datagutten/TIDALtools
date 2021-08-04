<?php

use datagutten\tools\files\files;

$config['input_path_id'] = files::path_join(__DIR__, 'sample_data', 'id_input');
$config['input_path_order'] = files::path_join(__DIR__, 'sample_data', 'order_input');
$config['output_path'] = files::path_join(__DIR__, 'rename_output');

return $config;