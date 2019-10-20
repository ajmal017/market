UPDATE exchange	SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'ICE FUTURES U.S.'),
	main_exchange_code = 'ICE'
	WHERE name IN (
		'ICE FUTURES U.S.',
		'ICE FUTURES ENERGY DIV'
	);

INSERT INTO exchange (name) VALUES ('Australian Securities Exchange'),
	('Chicago Board Options Exchange');
INSERT INTO exchange_code (exchange_id, code, main) VALUES (
	(SELECT id FROM exchange WHERE name = 'Australian Securities Exchange'),
	'ASX',
	true
);
INSERT INTO exchange_code (exchange_id, code, main) VALUES (
	(SELECT id FROM exchange WHERE name = 'Chicago Board Options Exchange'),
	'CBOE',
	true
);

UPDATE exchange SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'Australian Securities Exchange'),
	main_exchange_code = 'ASX'
	WHERE name IN (
		'Australian Securities Exchange'
	);

UPDATE exchange SET
	main_exchange_id = (SELECT id FROM exchange WHERE name = 'CHICAGO MERCANTILE EXCHANGE'),
	main_exchange_code = 'CME'
	WHERE name IN (
		'Chicago Board Options Exchange'
	);

ALTER TABLE contract
	DROP COLUMN sample,
	ALTER COLUMN year DROP NOT NULL,
	ALTER COLUMN month DROP NOT NULL,
	ADD COLUMN depth SMALLINT CHECK (depth > 0),
	ADD UNIQUE (instrument_id, depth),
	ADD CONSTRAINT standard_or_continuous CHECK (year IS NOT NULL AND month IS NOT NULL OR depth IS NOT NULL);

ALTER TABLE trade_day
	ALTER COLUMN open DROP NOT NULL,
	ADD COLUMN change NUMERIC,
	ADD COLUMN wave NUMERIC,
	ADD COLUMN previous_settlement NUMERIC,
	ADD COLUMN efp_volume NUMERIC,
	ADD COLUMN efs_volume NUMERIC,
	ADD COLUMN block_volume NUMERIC,
	ADD COLUMN last NUMERIC,
	ADD COLUMN open_interest NUMERIC;

DROP VIEW instrument_exchange;

ALTER TABLE instrument
	ALTER COLUMN name TYPE VARCHAR(128),
	ALTER COLUMN name_lower TYPE VARCHAR(128);
