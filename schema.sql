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

--
-- Name: round_sec(timestamp without time zone); Type: FUNCTION; Schema: public; Owner: default
--

CREATE FUNCTION round_sec(timestamp without time zone) RETURNS timestamp without time zone
    LANGUAGE sql IMMUTABLE
    AS $_$
select date_trunc('second', $1+interval '0.5 second')
$_$;


ALTER FUNCTION public.round_sec(timestamp without time zone) OWNER TO default;

--
-- Name: round_sec(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: default
--

CREATE FUNCTION round_sec(timestamp with time zone) RETURNS timestamp with time zone
    LANGUAGE sql IMMUTABLE
    AS $_$
select date_trunc('second', $1+interval '0.5 second')
$_$;


ALTER FUNCTION public.round_sec(timestamp with time zone) OWNER TO default;

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


ALTER TABLE public.sensor OWNER TO default;

--
-- Name: sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: default
--

CREATE SEQUENCE sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.sensor_id_seq OWNER TO default;

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


ALTER TABLE public.temps OWNER TO default;

--
-- Name: temp_stream; Type: VIEW; Schema: public; Owner: default
--

CREATE VIEW temp_stream AS
    SELECT temps.temp, temps.datetime, sensor.name FROM temps, sensor WHERE ((sensor.sensoraddr)::text = (temps.sensoraddr)::text);


ALTER TABLE public.temp_stream OWNER TO default;

--
-- Name: tempdiff; Type: VIEW; Schema: public; Owner: default
--

CREATE VIEW tempdiff AS
    SELECT (i.temp - u.temp) AS value, to_char(timezone('UTC'::text, i.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at, i.datetime FROM temp_stream i, temp_stream u WHERE (((round_sec(u.datetime) = round_sec(i.datetime)) AND ((u.name)::text = 'Ute'::text)) AND ((i.name)::text = 'Inne'::text));


ALTER TABLE public.tempdiff OWNER TO default;

--
-- Name: temps_id_seq; Type: SEQUENCE; Schema: public; Owner: default
--

CREATE SEQUENCE temps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.temps_id_seq OWNER TO default;

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
-- Name: idx_temps_datetime; Type: INDEX; Schema: public; Owner: default; Tablespace: 
--

CREATE INDEX idx_temps_datetime ON temps USING btree (datetime);


--
-- Name: idx_temps_datetimesec; Type: INDEX; Schema: public; Owner: default; Tablespace: 
--

CREATE INDEX idx_temps_datetimesec ON temps USING btree (round_sec(datetime));


--
-- Name: idx_temps_sensoraddr; Type: INDEX; Schema: public; Owner: default; Tablespace: 
--

CREATE INDEX idx_temps_sensoraddr ON temps USING btree (sensoraddr);


--
-- Name: idx_temps_termid; Type: INDEX; Schema: public; Owner: default; Tablespace: 
--

CREATE INDEX idx_temps_termid ON temps USING btree (termid);


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

