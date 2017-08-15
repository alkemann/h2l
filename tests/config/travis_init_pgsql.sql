CREATE SEQUENCE tests_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 18 CACHE 1;

CREATE TABLE "public"."tests" (
  "id" integer DEFAULT nextval('tests_id_seq') NOT NULL,
  "name" character varying(256) NOT NULL,
  "age" integer NOT NULL
) WITH (oids = false);