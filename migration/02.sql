CREATE TABLE exchange_code (
	exchange_id INTEGER NOT NULL,
	code VARCHAR(6) NOT NULL,
	UNIQUE (code),
	FOREIGN KEY (exchange_id) REFERENCES exchange
);

ALTER TABLE exchange
	ADD COLUMN main_exchange_id INTEGER,
	ADD COLUMN main_exchange_code VARCHAR(6),
	ADD FOREIGN KEY (main_exchange_id) REFERENCES exchange,
	ADD FOREIGN KEY (main_exchange_code) REFERENCES exchange_code (code);

INSERT INTO exchange_code (exchange_id, code) VALUES
	((SELECT id FROM exchange WHERE name = 'ICE FUTURES U.S.'), 'ICE'),
	((SELECT id FROM exchange WHERE name = 'CHICAGO BOARD OF TRADE'), 'CBOT'),
	((SELECT id FROM exchange WHERE name = 'CHICAGO MERCANTILE EXCHANGE'), 'CME'),
	((SELECT id FROM exchange WHERE name = 'NEW YORK MERCANTILE EXCHANGE'), 'NYMEX'),
	((SELECT id FROM exchange WHERE name = 'COMMODITY EXCHANGE INC.'), 'COMEX'),
	((SELECT id FROM exchange WHERE name = 'KANSAS CITY BOARD OF TRADE'), 'KCBT');

UPDATE exchange SET main_exchange_code = 'CME' WHERE name IN (
	'CHICAGO BOARD OF TRADE',
	'COMMODITY EXCHANGE INC.',
	'NEW YORK MERCANTILE EXCHANGE'
);

ALTER TABLE instrument
	ALTER COLUMN code TYPE VARCHAR(6),
	RENAME COLUMN code TO symbol,
	ALTER COLUMN exchange_id SET NOT NULL,
	ADD FOREIGN KEY (exchange_id) REFERENCES exchange;

ALTER TABLE contract
	DROP COLUMN code,
	DROP COLUMN decade;

CREATE VIEW contract_code AS
	SELECT contract.*,
		(
			SELECT instrument.code
			FROM instrument
			WHERE contract.instrument_id = instrument.id
		) || (
			SELECT month.code
			FROM month
			WHERE contract.month = month.month
		) || year AS code
	FROM contract;
