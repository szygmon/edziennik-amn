CREATE SEQUENCE School_id_seq INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE School (id INT NOT NULL, alias VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, ts TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));
CREATE UNIQUE INDEX UNIQ_B370AE9BE16C6B94 ON school (alias);