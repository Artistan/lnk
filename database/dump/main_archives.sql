INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (2, '1', '3', '', '2017-09-08 18:02:36', '2017-09-08 18:02:36', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (3, '3', '1', '[
	''location'' => function ($value, $record) {
      return [
      	$record[''mapX''],
      	$record[''mapY'']
      ];
    }
]', '2017-09-08 19:21:58', '2017-09-13 11:10:25', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (1, '2', '2', '', '2017-09-08 19:22:31', '2017-09-08 19:25:35', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (5, '4', '3', '', '2017-09-08 19:23:08', '2017-09-08 19:23:08', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (6, '6', '1', '[
	''location'' => function ($value, $record) {
      return [
      	$record[''mapX''],
      	$record[''mapY'']
      ];
    }
]', '2017-09-08 19:23:30', '2017-09-13 11:10:37', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (4, '5', '2', '', '2017-09-08 19:23:55', '2017-09-08 19:23:55', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (9, '8', '2', '', '2017-09-08 19:24:26', '2017-09-08 19:24:26', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (7, '9', '1', '[
	''location'' => function ($value, $record) {
      return [
      	$record[''mapX''],
      	$record[''mapY'']
      ];
    }
]', '2017-09-08 19:24:47', '2017-09-13 11:10:44', 'data');
INSERT INTO archives (source_id, group_id, mapping_id, transformers, created_at, updated_at, type) VALUES (8, '7', '3', '', '2017-09-08 19:25:11', '2017-09-08 19:25:11', 'data');