--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: addmeasure(double precision, integer); Type: FUNCTION; Schema: public; Owner: morten
--

CREATE FUNCTION addmeasure(double precision, integer) RETURNS integer
    LANGUAGE plpgsql
    AS $_$ declare
sval float:=$1;
sid integer:=$2;
mx float;
mn float;

begin
select minvalue,maxvalue into mn,mx from sensor where id=$2;
insert into measure(sensorid,value,use) values(sid,sval,mn<sval and mx >sval);
return 0;
end;
$_$;


ALTER FUNCTION public.addmeasure(double precision, integer) OWNER TO morten;

--
-- Name: addreading(numeric); Type: FUNCTION; Schema: public; Owner: morten
--

CREATE FUNCTION addreading(numeric) RETURNS timestamp with time zone
    LANGUAGE plpgsql
    AS $_$
declare
kwh numeric :=$1;
ts timestamp:='now';
mx numeric;
begin
select into mx max(reading) from powerreading;
while (kwh < mx)
loop
kwh:=kwh+100000;
end loop;
insert into powerreading (datetime,reading,read) values(ts,kwh,false);
return ts;
end;$_$;


ALTER FUNCTION public.addreading(numeric) OWNER TO morten;

--
-- Name: powerreading(); Type: FUNCTION; Schema: public; Owner: morten
--

CREATE FUNCTION powerreading() RETURNS timestamp without time zone
    LANGUAGE plpgsql
    AS $_$
declare kwh numeric := $1;
ts timestamp:='now';
mx numeric;                                                   
begin                                                         
select into mx max(reading) from powerreading;                
while (kwh < mx)                                              
loop                                                          
kwh:=kwh+100000;                                              
 end loop;                                                     
insert into powerreading (datetime,reading) values(ts,kwh);   
                                  return ts;                                                    
 end;                                                          
$_$;


ALTER FUNCTION public.powerreading() OWNER TO morten;

--
-- Name: round_sec(timestamp without time zone); Type: FUNCTION; Schema: public; Owner: morten
--

CREATE FUNCTION round_sec(timestamp without time zone) RETURNS timestamp without time zone
    LANGUAGE sql IMMUTABLE
    AS $_$
select date_trunc('second', $1+interval '0.5 second')
$_$;


ALTER FUNCTION public.round_sec(timestamp without time zone) OWNER TO morten;

--
-- Name: round_sec(timestamp with time zone); Type: FUNCTION; Schema: public; Owner: morten
--

CREATE FUNCTION round_sec(timestamp with time zone) RETURNS timestamp with time zone
    LANGUAGE sql IMMUTABLE
    AS $_$
select date_trunc('second', $1+interval '0.5 second')
$_$;


ALTER FUNCTION public.round_sec(timestamp with time zone) OWNER TO morten;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: measure; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE measure (
    id integer NOT NULL,
    sensorid integer,
    type integer,
    value double precision,
    datetime timestamp with time zone DEFAULT now(),
    use boolean DEFAULT true,
    aux integer,
    payload integer
);


ALTER TABLE measure OWNER TO morten;

--
-- Name: measure_qa; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW measure_qa AS
 SELECT measure.id,
    measure.sensorid,
    measure.type AS typeid,
    measure.value,
    measure.datetime,
    measure.use
   FROM measure
  WHERE (measure.use = true);


ALTER TABLE measure_qa OWNER TO morten;

--
-- Name: daymax; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW daymax AS
 SELECT max(measure.value) AS value,
    (measure.datetime)::date AS datetime,
    measure.sensorid
   FROM measure_qa measure
  GROUP BY (measure.datetime)::date, measure.sensorid;


ALTER TABLE daymax OWNER TO morten;

--
-- Name: daymean; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW daymean AS
 SELECT (round(((100)::double precision * avg(measure.value))) / (100)::double precision) AS value,
    (measure.datetime)::date AS datetime,
    measure.sensorid
   FROM measure_qa measure
  GROUP BY (measure.datetime)::date, measure.sensorid;


ALTER TABLE daymean OWNER TO morten;

--
-- Name: daymin; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW daymin AS
 SELECT min(measure.value) AS value,
    (measure.datetime)::date AS datetime,
    measure.sensorid
   FROM measure_qa measure
  GROUP BY (measure.datetime)::date, measure.sensorid;


ALTER TABLE daymin OWNER TO morten;

--
-- Name: measure_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE measure_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE measure_id_seq OWNER TO morten;

--
-- Name: measure_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE measure_id_seq OWNED BY measure.id;


--
-- Name: powerreading; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE powerreading (
    id integer NOT NULL,
    datetime timestamp with time zone,
    reading double precision,
    read boolean DEFAULT true
);


ALTER TABLE powerreading OWNER TO morten;

--
-- Name: powerdraw; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW powerdraw AS
 SELECT powerreading.id,
    powerreading.datetime,
    lag(powerreading.datetime) OVER (ORDER BY powerreading.id) AS starttime,
    (powerreading.reading - lag(powerreading.reading) OVER (ORDER BY powerreading.id)) AS kwh,
    (date_part('epoch'::text, (powerreading.datetime - lag(powerreading.datetime) OVER (ORDER BY powerreading.id))) / (3600)::double precision) AS hours
   FROM powerreading;


ALTER TABLE powerdraw OWNER TO morten;

--
-- Name: powerreading_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE powerreading_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE powerreading_id_seq OWNER TO morten;

--
-- Name: powerreading_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE powerreading_id_seq OWNED BY powerreading.id;


--
-- Name: sensor; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE sensor (
    id integer NOT NULL,
    name character varying(255),
    sensoraddr character varying(255),
    type character varying NOT NULL,
    minvalue double precision,
    maxvalue double precision,
    maxdelta double precision,
    typeid integer,
    active boolean DEFAULT true,
    stationid integer NOT NULL,
    factor double precision DEFAULT 1 NOT NULL
);


ALTER TABLE sensor OWNER TO morten;

--
-- Name: sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE sensor_id_seq OWNER TO morten;

--
-- Name: sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE sensor_id_seq OWNED BY sensor.id;


--
-- Name: station; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE station (
    id integer NOT NULL,
    name character varying NOT NULL,
    datetime timestamp with time zone DEFAULT now()
);


ALTER TABLE station OWNER TO morten;

--
-- Name: type; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE type (
    id integer NOT NULL,
    name character varying,
    unit character varying
);


ALTER TABLE type OWNER TO morten;

--
-- Name: sensorlist; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW sensorlist AS
 SELECT sensor.id,
    concat(type.name, ' på ', station.name) AS concat
   FROM station,
    sensor,
    type
  WHERE ((station.id = sensor.stationid) AND (type.id = sensor.typeid))
  ORDER BY station.name;


ALTER TABLE sensorlist OWNER TO morten;

--
-- Name: sensormeasurement; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW sensormeasurement AS
 SELECT sensor.id AS sensorid,
    (measure.value / sensor.factor) AS value,
    to_char(timezone('UTC'::text, measure.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at,
    measure.datetime
   FROM sensor,
    measure
  WHERE (((sensor.typeid = measure.type) AND (sensor.stationid = measure.sensorid)) AND (measure.use = true));


ALTER TABLE sensormeasurement OWNER TO morten;

--
-- Name: sensors; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW sensors AS
 SELECT sensor.id,
    sensor.name,
    sensor.sensoraddr,
    sensor.type,
    sensor.minvalue,
    sensor.maxvalue,
    sensor.maxdelta,
    sensor.typeid,
    type.name AS measurement,
    type.unit
   FROM sensor,
    type
  WHERE (type.id = sensor.typeid);


ALTER TABLE sensors OWNER TO morten;

--
-- Name: shadow; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW shadow AS
 SELECT LEAST(u.value, i.value) AS value,
    to_char(timezone('UTC'::text, i.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at,
    i.datetime
   FROM measure_qa i,
    measure_qa u
  WHERE (((round_sec(u.datetime) = round_sec(i.datetime)) AND (u.sensorid = 1)) AND (i.sensorid = 10));


ALTER TABLE shadow OWNER TO morten;

--
-- Name: temps; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE temps (
    id integer NOT NULL,
    termid integer,
    temp double precision,
    datetime timestamp with time zone DEFAULT now(),
    sensoraddr character varying(20)
);


ALTER TABLE temps OWNER TO morten;

--
-- Name: temp_stream; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW temp_stream AS
 SELECT temps.temp,
    temps.datetime,
    sensor.name
   FROM temps,
    sensor
  WHERE ((sensor.sensoraddr)::text = (temps.sensoraddr)::text);


ALTER TABLE temp_stream OWNER TO morten;

--
-- Name: tempdiff; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW tempdiff AS
 SELECT (i.value - u.value) AS value,
    to_char(timezone('UTC'::text, i.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at,
    i.datetime
   FROM measure_qa i,
    measure_qa u
  WHERE (((round_sec(u.datetime) = round_sec(i.datetime)) AND (u.sensorid = 1)) AND (i.sensorid = 2));


ALTER TABLE tempdiff OWNER TO morten;

--
-- Name: tempdiff_old; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW tempdiff_old AS
 SELECT (i.temp - u.temp) AS value,
    to_char(timezone('UTC'::text, i.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at,
    i.datetime
   FROM temp_stream i,
    temp_stream u
  WHERE (((round_sec(u.datetime) = round_sec(i.datetime)) AND ((u.name)::text = 'Ute'::text)) AND ((i.name)::text = 'Inne'::text));


ALTER TABLE tempdiff_old OWNER TO morten;

--
-- Name: tempdiff_sol; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW tempdiff_sol AS
 SELECT (u.value - i.value) AS value,
    to_char(timezone('UTC'::text, i.datetime), 'yyyy-mm-dd"T"HH24:MI:SS"Z"'::text) AS at,
    i.datetime
   FROM measure_qa i,
    measure_qa u
  WHERE (((round_sec(u.datetime) = round_sec(i.datetime)) AND (u.sensorid = 1)) AND (i.sensorid = 10));


ALTER TABLE tempdiff_sol OWNER TO morten;

--
-- Name: temps_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE temps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temps_id_seq OWNER TO morten;

--
-- Name: temps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE temps_id_seq OWNED BY temps.id;


--
-- Name: type_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE type_id_seq OWNER TO morten;

--
-- Name: type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE type_id_seq OWNED BY type.id;


--
-- Name: view_all_grants; Type: VIEW; Schema: public; Owner: morten
--

CREATE VIEW view_all_grants AS
 SELECT via.subject,
    via.namespace,
    via.relname,
    via.relkind,
    via.owner,
    via.relacl,
    via.relaclitemuser,
    via.via_owner,
    via.via_groupowner,
    via.via_user,
    via.via_group,
    via.via_public
   FROM ( SELECT use.usename AS subject,
            nsp.nspname AS namespace,
            c.relname,
            c.relkind,
            pg_authid.rolname AS owner,
            c.relacl,
            c.relaclitemuser,
            (use.usename = pg_authid.rolname) AS via_owner,
                CASE
                    WHEN (use.usename = pg_authid.rolname) THEN false
                    ELSE pg_has_role(use.usename, pg_authid.rolname, 'member'::text)
                END AS via_groupowner,
            ((use.usename)::text = c.relaclitemuser) AS via_user,
                CASE
                    WHEN (c.relaclitemuser = ''::text) THEN false
                    WHEN (c.relaclitemuser = '!NULL!'::text) THEN false
                    WHEN (c.relaclitemuser = (use.usename)::text) THEN false
                    ELSE pg_has_role(use.usename, (c.relaclitemuser)::name, 'member'::text)
                END AS via_group,
            (c.relaclitemuser = ''::text) AS via_public
           FROM (((pg_user use
             CROSS JOIN ( SELECT sub_c.relnamespace,
                    sub_c.relname,
                    sub_c.relkind,
                    sub_c.relowner,
                    sub_c.relacl,
                    sub_c.relaclitem,
                    split_part(sub_c.relaclitem, '='::text, 1) AS relaclitemuser
                   FROM ( SELECT pg_class.relnamespace,
                            pg_class.relname,
                            pg_class.relkind,
                            pg_class.relowner,
                            pg_class.relacl,
                                CASE
                                    WHEN (pg_class.relacl IS NULL) THEN '!NULL!='::text
                                    ELSE unnest((pg_class.relacl)::text[])
                                END AS relaclitem
                           FROM pg_class) sub_c) c)
             LEFT JOIN pg_namespace nsp ON ((c.relnamespace = nsp.oid)))
             LEFT JOIN pg_authid ON ((c.relowner = pg_authid.oid)))) via
  WHERE ((((via.via_owner OR via.via_groupowner) OR via.via_user) OR via.via_group) OR via.via_public)
  ORDER BY via.subject, via.namespace, via.relname;


ALTER TABLE view_all_grants OWNER TO morten;

--
-- Name: wireless; Type: TABLE; Schema: public; Owner: morten; Tablespace: 
--

CREATE TABLE wireless (
    id integer NOT NULL,
    sensorid integer NOT NULL,
    value integer NOT NULL,
    type integer NOT NULL,
    aux integer,
    payload integer,
    addtime timestamp with time zone DEFAULT now()
);


ALTER TABLE wireless OWNER TO morten;

--
-- Name: wireless_id_seq; Type: SEQUENCE; Schema: public; Owner: morten
--

CREATE SEQUENCE wireless_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wireless_id_seq OWNER TO morten;

--
-- Name: wireless_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: morten
--

ALTER SEQUENCE wireless_id_seq OWNED BY wireless.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: morten
--

ALTER TABLE ONLY measure ALTER COLUMN id SET DEFAULT nextval('measure_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: morten
--

ALTER TABLE ONLY powerreading ALTER COLUMN id SET DEFAULT nextval('powerreading_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: morten
--

ALTER TABLE ONLY sensor ALTER COLUMN id SET DEFAULT nextval('sensor_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: morten
--

ALTER TABLE ONLY temps ALTER COLUMN id SET DEFAULT nextval('temps_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: morten
--

ALTER TABLE ONLY wireless ALTER COLUMN id SET DEFAULT nextval('wireless_id_seq'::regclass);


--
-- Name: measure_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY measure
    ADD CONSTRAINT measure_pkey PRIMARY KEY (id);


--
-- Name: powerreading_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY powerreading
    ADD CONSTRAINT powerreading_pkey PRIMARY KEY (id);


--
-- Name: sensor_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_pkey PRIMARY KEY (id);


--
-- Name: station_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY station
    ADD CONSTRAINT station_pkey PRIMARY KEY (id);


--
-- Name: temps_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY temps
    ADD CONSTRAINT temps_pkey PRIMARY KEY (id);


--
-- Name: type_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY type
    ADD CONSTRAINT type_pkey PRIMARY KEY (id);


--
-- Name: wireless_pkey; Type: CONSTRAINT; Schema: public; Owner: morten; Tablespace: 
--

ALTER TABLE ONLY wireless
    ADD CONSTRAINT wireless_pkey PRIMARY KEY (id);


--
-- Name: idx_temps_datetime; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX idx_temps_datetime ON temps USING btree (datetime);


--
-- Name: idx_temps_datetimesec; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX idx_temps_datetimesec ON temps USING btree (round_sec(datetime));


--
-- Name: idx_temps_sensoraddr; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX idx_temps_sensoraddr ON temps USING btree (sensoraddr);


--
-- Name: idx_temps_termid; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX idx_temps_termid ON temps USING btree (termid);


--
-- Name: measure_datetime_idx; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX measure_datetime_idx ON measure USING btree (datetime);


--
-- Name: measure_sensorid_datetime_idx; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX measure_sensorid_datetime_idx ON measure USING btree (sensorid, datetime);


--
-- Name: measure_sensorid_datetime_idx1; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX measure_sensorid_datetime_idx1 ON measure USING btree (sensorid, datetime DESC);


--
-- Name: measure_sensorid_idx; Type: INDEX; Schema: public; Owner: morten; Tablespace: 
--

CREATE INDEX measure_sensorid_idx ON measure USING btree (sensorid);


--
-- Name: sensor_stationid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: morten
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_stationid_fkey FOREIGN KEY (stationid) REFERENCES station(id);


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

