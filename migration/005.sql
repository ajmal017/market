UPDATE exchange SET
	main_exchange_id = (SELECT exchange_id FROM exchange_code WHERE code = 'CME'),
	main_exchange_code = 'CME'
	WHERE name IN (
		'KANSAS CITY BOARD OF TRADE'
	);

ALTER TABLE exchange_code
	ADD COLUMN main BOOLEAN DEFAULT true;

UPDATE exchange_code SET main = NULL WHERE code IN ('ICU', 'ICUS', 'CBT', 'NYME', 'CMX');

ALTER TABLE exchange_code
	ALTER COLUMN main SET DEFAULT NULL;

INSERT INTO exchange_code (exchange_id, code) VALUES
	((SELECT exchange_id FROM exchange_code WHERE code = 'NYMEX'), 'NY');

ALTER TABLE exchange_code
	ADD CONSTRAINT use_null_for_secondary_exchange_codes CHECK (main != false),
	ADD UNIQUE (exchange_id, main);

ALTER TABLE instrument
	ADD COLUMN name_lower VARCHAR(64),
	DROP CONSTRAINT instrument_name_key,
	ADD UNIQUE (name_lower);

ALTER TABLE contract
	DROP CONSTRAINT contract_instrument_id_fkey,
	ADD FOREIGN KEY (instrument_id) REFERENCES instrument ON DELETE CASCADE ON UPDATE CASCADE;

DELETE FROM instrument WHERE symbol IN ('W', 'KW', 'HG', 'HO', 'B', 'CT', 'OJ', 'G', 'RB');

INSERT INTO instrument (exchange_id, name, name_lower, symbol) VALUES
	((SELECT exchange_id FROM exchange_code WHERE code = 'CBOT'), 'Wheat-SRW', 'wheat-srw', 'W'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'KCBT'), 'Wheat-HRW', 'wheat-hrw', 'KW'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'COMEX'), 'Copper', 'copper-grade #1', 'HG'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'NYMEX'), 'Heating Oil', '#2 heating oil, ny harbor-ulsd', 'HO'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'ICE'), 'Brent Crude Oil', 'brent crude oil last day', 'B'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'ICE'), 'Cotton', 'cotton no. 2', 'CT'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'ICE'), 'Orange Juice', 'frzn concentrated orange juice', 'OJ'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'IFED'), 'Gasoil', 'gasoline crk-rbob/brent 1st', 'G'),
	((SELECT exchange_id FROM exchange_code WHERE code = 'NYMEX'), 'Gasoline', 'gasoline blendstock (rbob)', 'RB')
	ON CONFLICT (name_lower) DO UPDATE SET symbol = EXCLUDED.symbol;

UPDATE instrument SET name_lower = lower(name) WHERE name_lower IS NULL AND name IS NOT NULL;

ALTER TABLE contract
	ADD COLUMN description TEXT,
	ADD COLUMN refreshed_at DATE,
	ADD COLUMN from_date DATE,
	ADD COLUMN to_date DATE;

CREATE VIEW instrument_exchange AS
	SELECT exchange_code.code AS exchange_code, instrument.*
	FROM instrument
	JOIN exchange_code ON instrument.exchange_id = exchange_code.exchange_id
		AND exchange_code.main;
