CREATE TABLE exchange (
	id SERIAL NOT NULL,
	name VARCHAR(64) NOT NULL,
	same_exchange_id INTEGER,
	PRIMARY KEY (id),
	UNIQUE (name),
	FOREIGN KEY (same_exchange_id) REFERENCES exchange
);

INSERT INTO exchange (name) VALUES ('ICE FUTURES U.S.'),
	('CHICAGO BOARD OF TRADE'),
	('ICE FUTURES ENERGY DIV');
INSERT INTO exchange (name, same_exchange_id) VALUES ('ICE FUTURES ENERGY D', 3);

CREATE TABLE instrument (
	id SERIAL NOT NULL,
	exchange_id INTEGER,
	name VARCHAR(64),
	code CHAR(2),
	contract_volume VARCHAR(64),
	PRIMARY KEY (id),
	UNIQUE (name),
	UNIQUE (code)
);

INSERT INTO instrument (code, name, exchange_id, contract_volume) VALUES ('CC', 'COCOA', 1, '10 METRIC TONS'),
	('ZL', 'CORN', 2, '5,000 BUSHELS');

CREATE TABLE cot (
	date DATE NOT NULL,
	instrument_id INTEGER NOT NULL,
	hedgers_long INTEGER NOT NULL CHECK (hedgers_long >= 0),
	hedgers_short INTEGER NOT NULL CHECK (hedgers_short <= 0),
	swap_long INTEGER NOT NULL CHECK (swap_long >= 0),
	swap_short INTEGER NOT NULL CHECK (swap_short <= 0),
	managed_long INTEGER NOT NULL CHECK (managed_long >= 0),
	managed_short INTEGER NOT NULL CHECK (managed_short <= 0),
	other_long INTEGER NOT NULL CHECK (other_long >= 0),
	other_short INTEGER NOT NULL CHECK (other_short <= 0),
	PRIMARY KEY (date, instrument_id)
);

INSERT INTO cot (instrument_id, date, hedgers_long, hedgers_short, swap_long, swap_short, managed_long, managed_short, other_long, other_short) VALUES
	(1, '2017-05-02', 139879, -121346, 26651, -20725, 47780, -92569, 22675, -12650),
	(2, '2017-05-02', 443571, -572306, 236138, -24529, 185081, -369711, 128505, -56950);

CREATE TABLE month (
	month SMALLINT NOT NULL,
	code CHAR(1) NOT NULL,
	PRIMARY KEY (month),
	UNIQUE (code)
);

INSERT INTO month (month, code) VALUES (1, 'F'),
	(2, 'G'),
	(3, 'H'),
	(4, 'J'),
	(5, 'K'),
	(6, 'M'),
	(7, 'N'),
	(8, 'Q'),
	(9, 'U'),
	(10, 'V'),
	(11, 'X'),
	(12, 'Z');

CREATE TABLE contract (
	id SERIAL NOT NULL,
	code CHAR(4) NOT NULL,
	instrument_id INTEGER NOT NULL,
	decade SMALLINT NOT NULL,
	year SMALLINT NOT NULL,
	month INTEGER NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (code, decade),
	UNIQUE (instrument_id, year, month),
	CONSTRAINT valid_year CHECK (year >= decade AND year < decade + 10),
	FOREIGN KEY (instrument_id) REFERENCES instrument,
	FOREIGN KEY (month) REFERENCES month
);

INSERT INTO contract (code, instrument_id, decade, year, month) VALUES ('CCN7', 1, 2010, 2017, 7),
	('ZLN7', 2, 2010, 2017, 7),
	('CCU7', 1, 2010, 2017, 9);

CREATE TABLE trade_day (
	date DATE NOT NULL,
	contract_id INTEGER NOT NULL,
	open NUMERIC NOT NULL,
	high NUMERIC CONSTRAINT high_is_highest CHECK (high >= open AND high >= low AND high >= close),
	low NUMERIC CONSTRAINT low_is_lowest CHECK (low <= open AND low <= high AND low <= close),
	close NUMERIC,
	volume INTEGER,
	PRIMARY KEY (date, contract_id),
	FOREIGN KEY (contract_id) REFERENCES contract
	--check date <= contract execution date
);

INSERT INTO trade_day (contract_id, date, open, high, low, close) VALUES (1, '2017-05-04', 1775, 1844, 1722, 1839),
	(1, '2017-05-05', 1850, 1877, 1805, 1866),
	(1, '2017-05-08', 1881, 1964, 1876, 1955),
	(1, '2017-05-09', 1969, 1971, 1926, 1960),
	(1, '2017-05-10', 1963, 1968, 1939, 1954),
	(1, '2017-05-11', 1954, 1964, 1941, 1956);
