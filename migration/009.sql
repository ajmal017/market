INSERT INTO exchange (name) VALUES ('Eurex Exchange'),
	('Hong Kong Exchange');

INSERT INTO exchange_code (exchange_id, code, main) VALUES (
		(SELECT id FROM exchange WHERE name = 'Eurex Exchange'),
		'EUREX',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Hong Kong Exchange'),
		'HKEX',
		true
	);

UPDATE exchange SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'Eurex Exchange'),
	main_exchange_code = 'EUREX'
	WHERE name IN (
		'Eurex Exchange'
	);

UPDATE exchange SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'Hong Kong Exchange'),
	main_exchange_code = 'HKEX'
	WHERE name IN (
		'Hong Kong Exchange'
	);

ALTER TABLE trade_day
	ADD COLUMN bid NUMERIC,
	ADD COLUMN ask NUMERIC;
