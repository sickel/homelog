--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: sensor; Type: TABLE; Schema: public; Owner: default; Tablespace: 
--

CREATE TABLE sensor (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    sensoraddr character varying(255) NOT NULL
);


--
-- Name: sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: default
--

CREATE SEQUENCE sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- Name: sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: default
--

ALTER SEQUENCE sensor_id_seq OWNED BY sensor.id;


--
-- Name: temps; Type: TABLE; Schema: public; Owner: default; Tablespace: 
--

CREATE TABLE temps (
    id integer NOT NULL,
    termid integer,
    temp double precision,
    datetime timestamp with time zone DEFAULT now(),
    sensoraddr character varying(20)
);



--
-- Name: temp_stream; Type: VIEW; Schema: public; Owner: default
--

CREATE VIEW temp_stream AS
    SELECT temps.temp, temps.datetime, sensor.name FROM temps, sensor WHERE ((sensor.sensoraddr)::text = (temps.sensoraddr)::text);



--
-- Name: temps_id_seq; Type: SEQUENCE; Schema: public; Owner: default
--

CREATE SEQUENCE temps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- Name: temps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: default
--

ALTER SEQUENCE temps_id_seq OWNED BY temps.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: default
--

ALTER TABLE ONLY sensor ALTER COLUMN id SET DEFAULT nextval('sensor_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: default
--

ALTER TABLE ONLY temps ALTER COLUMN id SET DEFAULT nextval('temps_id_seq'::regclass);


--
-- Name: sensor_pkey; Type: CONSTRAINT; Schema: public; Owner: default; Tablespace: 
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_pkey PRIMARY KEY (id);


--
-- Name: temps_pkey; Type: CONSTRAINT; Schema: public; Owner: default; Tablespace: 
--

ALTER TABLE ONLY temps
    ADD CONSTRAINT temps_pkey PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

