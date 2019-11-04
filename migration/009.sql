INSERT INTO exchange (name) VALUES ('Eurex Exchange'),
	('Hong Kong Exchange');
INSERT INTO exchange (name) VALUES ('Multi Commodity Exchange Of India'),
	('Montreal Exchange'),
	('Osaka Dojima Commodity Exchange'),
	('Osaka Securities Exchange'),
	('Singapore Exchange'),
	('Shanghai Futures Exchange'),
	('Tokyo Financial Exchange');
INSERT INTO exchange (name, main_exchange_id, main_exchange_code) VALUES
	('London International Financial Futures and Options Exchange',
		(SELECT exchange_id FROM exchange_code WHERE code = 'ICE'),
		'ICE'
	),
	('Minneapolis Grain Exchange',
		(SELECT exchange_id FROM exchange_code WHERE code = 'CME'),
		'CME'
	);

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

INSERT INTO exchange_code (exchange_id, code, main) VALUES (
		(SELECT id FROM exchange WHERE name = 'London International Financial Futures and Options Exchange'),
		'LIFFE',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Multi Commodity Exchange Of India'),
		'MCX',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Minneapolis Grain Exchange'),
		'MGEX',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Montreal Exchange'),
		'MX',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Osaka Dojima Commodity Exchange'),
		'ODE',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Osaka Securities Exchange'),
		'OSE',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Singapore Exchange'),
		'SGX',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Shanghai Futures Exchange'),
		'SHFE',
		true
	), (
		(SELECT id FROM exchange WHERE name = 'Tokyo Financial Exchange'),
		'TFX',
		true
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'LIFFE'
	WHERE name IN (
		'London International Financial Futures and Options Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'MCX'
	WHERE name IN (
		'Multi Commodity Exchange Of India'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'MGEX'
	WHERE name IN (
		'Minneapolis Grain Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'MX'
	WHERE name IN (
		'Montreal Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'ODE'
	WHERE name IN (
		'Osaka Dojima Commodity Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'OSE'
	WHERE name IN (
		'Osaka Securities Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'SGX'
	WHERE name IN (
		'Singapore Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'SHFE'
	WHERE name IN (
		'Shanghai Futures Exchange'
	);

UPDATE exchange SET
	main_exchange_id = id,
	main_exchange_code = 'TFX'
	WHERE name IN (
		'Tokyo Financial Exchange'
	);

ALTER TABLE trade_day
	ADD COLUMN bid NUMERIC,
	ADD COLUMN ask NUMERIC;

ALTER TABLE trade_day
	ADD COLUMN bid_size NUMERIC;
ALTER TABLE trade_day
	ADD COLUMN ask_size NUMERIC;
ALTER TABLE trade_day
	ADD COLUMN total NUMERIC,
	ADD COLUMN trades NUMERIC,
	ADD COLUMN implied_volatility NUMERIC;
