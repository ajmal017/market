ALTER TABLE instrument
	DROP CONSTRAINT instrument_name_key,
	DROP CONSTRAINT instrument_name_lower_key,
	ADD UNIQUE (name, exchange_id),
	ADD UNIQUE (name_lower, exchange_id);

ALTER TABLE contract
	ADD COLUMN sample BOOLEAN NOT NULL DEFAULT false;

UPDATE exchange SET main_exchange_id = id, main_exchange_code = 'CME' WHERE name = 'CHICAGO MERCANTILE EXCHANGE';
