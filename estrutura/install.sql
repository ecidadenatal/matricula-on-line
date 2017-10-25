-- Tabela ciclos
create table plugins.ciclos (
  mo09_codigo    serial primary key,
  mo09_status    bool not null default false,
  mo09_dtcad     date not null,
  mo09_descricao varchar(100) not null,
  mo09_sigla     varchar(10),
  mo09_eja       bool default false
);

alter table plugins.ciclos owner to plugin;

-- Tabela fase
create table plugins.fase (
mo04_codigo     serial primary key,
mo04_desc       varchar(100) not null ,
mo04_anousu     int4 not null default 0,
mo04_dtfim      date not null default null,
mo04_dtini      date not null default null,
mo04_ciclo      int8 default 0,
mo04_datacorte  date default null,
mo04_processada bool default 'f'
);

alter table plugins.fase owner to plugin;
alter table plugins.fase add constraint fase_ciclo_fk foreign key (mo04_ciclo) references plugins.ciclos;

create index fase_ciclo_in on plugins.fase(mo04_ciclo);

-- Tabela ciclosensino
create table plugins.ciclosensino (
  mo14_sequencial serial primary key,
  mo14_ciclo      int4 not null,
  mo14_ensino     int4 not null,
  constraint ciclosensino_ciclo_fk foreign key (mo14_ciclo) references plugins.ciclos (mo09_codigo) match full
);

alter table plugins.ciclosensino owner to plugin;

-- vagas
CREATE TABLE plugins.vagas(
  mo10_codigo     serial primary key,
  mo10_fase       int8 default 0,
  mo10_escola     int8 default 0,
  mo10_ensino     int8 default 0,
  mo10_serie      int8 default 0,
  mo10_turno      int8 default 0,
  mo10_numvagas   int4 default 0,
  mo10_saldovagas int4 default 0
);

ALTER TABLE plugins.vagas ADD CONSTRAINT vagas_fase_fk FOREIGN KEY (mo10_fase) REFERENCES plugins.fase;

alter table plugins.vagas owner to plugin;

-- tiporesp
CREATE TABLE plugins.tiporesp(
  mo06_codigo serial primary key,
  mo06_descr  varchar(100)
);

INSERT INTO plugins.tiporesp VALUES (1, 'PAI');
INSERT INTO plugins.tiporesp VALUES (2, 'MAE');
INSERT INTO plugins.tiporesp VALUES (3, 'PROPRIO');
INSERT INTO plugins.tiporesp VALUES (4, 'OUTROS');

select setval('plugins.tiporesp_mo06_codigo_seq', 4);
alter table plugins.tiporesp owner to plugin;

 -- redeorigem
CREATE TABLE plugins.redeorigem(
  mo05_codigo serial primary key,
  mo05_descr  varchar(100)
);

INSERT INTO plugins.redeorigem VALUES (1 , 'FEDERAL');
INSERT INTO plugins.redeorigem VALUES (2 , 'ESTADUAL');
INSERT INTO plugins.redeorigem VALUES (3 , 'MUNICIPAL');
INSERT INTO plugins.redeorigem VALUES (4 , 'PARTICULAR');
INSERT INTO plugins.redeorigem VALUES (5 , 'MST');
INSERT INTO plugins.redeorigem VALUES (6 , 'INCRA');
INSERT INTO plugins.redeorigem VALUES (7 , 'AFASTADO');
INSERT INTO plugins.redeorigem VALUES (8 , 'INICIAL');

select setval('plugins.redeorigem_mo05_codigo_seq', 8);
alter table plugins.redeorigem owner to plugin;

-- tabela idadeetapa
CREATE TABLE plugins.idadeetapa(
  mo15_sequencial   serial primary key,
  mo15_etapa        int4 NOT NULL default 0,
  mo15_idadeinicial interval,
  mo15_idadefinal   interval
);

CREATE INDEX idadeetapa_etapa_in ON plugins.idadeetapa(mo15_etapa);

alter table plugins.idadeetapa owner to plugin;

-- tabela estcivil
CREATE TABLE plugins.estcivil (
  mo07_codigo serial primary key,
  mo07_descr  varchar(50) not null
);

ALTER TABLE plugins.estcivil owner to plugin;

INSERT INTO plugins.estcivil (mo07_codigo, mo07_descr) VALUES ( 1, 'SOLTEIRO' );
INSERT INTO plugins.estcivil (mo07_codigo, mo07_descr) VALUES ( 2, 'CASADO' );
INSERT INTO plugins.estcivil (mo07_codigo, mo07_descr) VALUES ( 3, 'VIÚVO' );
INSERT INTO plugins.estcivil (mo07_codigo, mo07_descr) VALUES ( 4, 'DIVORCIADO' );

select setval('plugins.estcivil_mo07_codigo_seq', 4);

-- tabela escbairro
CREATE TABLE plugins.escbairro (
  mo08_codigo serial primary key,
  mo08_escola int4 NOT NULL default 0,
  mo08_bairro int4 default 0
);

ALTER TABLE plugins.escbairro owner to plugin;

CREATE INDEX escbairro_bairro_in ON plugins.escbairro(mo08_bairro);
CREATE INDEX escbairro_escola_in ON plugins.escbairro(mo08_escola);

-- Tabela mobase
CREATE TABLE plugins.mobase(
  mo01_codigo                 serial primary key,
  mo01_nome                   varchar(70) NOT NULL,
  mo01_tipoender              varchar(10),
  mo01_ender                  varchar(100) NOT NULL,
  mo01_numero                 varchar(10),
  mo01_compl                  varchar(20) NOT NULL,
  mo01_bairro                 int4 NOT NULL default 0,
  mo01_cep                    varchar(20) NOT NULL,
  mo01_uf                     varchar(5),
  mo01_municip                varchar(100) NOT NULL,
  mo01_nacion                 varchar(40) NOT NULL,
  mo01_telef                  varchar(12),
  mo01_ident                  varchar(20),
  mo01_orgident               varchar(20),
  mo01_tiporesp               int4 NOT NULL default 0,
  mo01_nomeresp               varchar(70),
  mo01_telresp                varchar(12),
  mo01_identresp              varchar(20),
  mo01_orgidresp              varchar(20),
  mo01_cpfresp                varchar(11),
  mo01_emailresp              varchar(50),
  mo01_certidaotipo           int4  default 0,
  mo01_certidaonum            varchar(8),
  mo01_certidaolivro          varchar(8),
  mo01_certidaofolha          varchar(4),
  mo01_certidaocart           varchar(150),
  mo01_ufcartcert             int4  default 0,
  mo01_muncartcert            int4  default 0,
  mo01_certidaodata           date  default null,
  mo01_nis                    varchar(11),
  mo01_dtnasc                 date NOT NULL default null,
  mo01_ufnasc                 int4  default 0,
  mo01_munnasc                int4  default 0,
  mo01_estciv                 int4  default 0,
  mo01_cpf                    varchar(11),
  mo01_email                  varchar(100),
  mo01_mae                    varchar(70),
  mo01_pai                    varchar(70),
  mo01_sexo                   char(1) NOT NULL,
  mo01_telcel                 varchar(12),
  mo01_serie                  int4 NOT NULL default 0,
  mo01_redeorigem             int4 NOT NULL default 0,
  mo01_datacad                date NOT NULL default null,
  mo01_necess                 int4  default 0,
  mo01_certidaomatricula      varchar(32),
  mo01_bolsafamilia           boolean default false,
  mo01_responsaveltrabalhador boolean default false
);

ALTER TABLE plugins.mobase ADD CONSTRAINT mobase_tiporesp_fk   FOREIGN KEY (mo01_tiporesp)   REFERENCES plugins.tiporesp;
ALTER TABLE plugins.mobase ADD CONSTRAINT mobase_estciv_fk     FOREIGN KEY (mo01_estciv)     REFERENCES plugins.estcivil;
ALTER TABLE plugins.mobase ADD CONSTRAINT mobase_redeorigem_fk FOREIGN KEY (mo01_redeorigem) REFERENCES plugins.redeorigem;

CREATE  INDEX mobase_bairro_in     ON plugins.mobase(mo01_bairro);
CREATE  INDEX mobase_cpfresp_in    ON plugins.mobase(mo01_cpfresp);
CREATE  INDEX mobase_nome_in       ON plugins.mobase(mo01_nome);
CREATE  INDEX mobase_redeorigem_in ON plugins.mobase(mo01_redeorigem);
CREATE  INDEX mobase_serie_in      ON plugins.mobase(mo01_serie);

alter table plugins.mobase owner to plugin;

-- tabela basefase
CREATE TABLE plugins.basefase(
  mo12_codigo serial primary key,
  mo12_base   int4 NOT NULL default 0,
  mo12_fase   int4 NOT NULL default 0,
  mo12_status bool default 'false'
);

ALTER TABLE plugins.basefase
  ADD CONSTRAINT basefase_fase_fk FOREIGN KEY (mo12_fase) REFERENCES plugins.fase,
  ADD CONSTRAINT basefase_base_fk FOREIGN KEY (mo12_base) REFERENCES plugins.mobase;

CREATE INDEX basefase_fase_in ON plugins.basefase(mo12_fase);
CREATE INDEX basefase_base_in ON plugins.basefase(mo12_base);

alter table plugins.basefase owner to plugin;

-- Tabela basenecess
create table plugins.basenecess (
  mo11_codigo serial primary key,
  mo11_base   int4 not null,
  mo11_necess int4 not null,
  mo11_status bool default false,
  constraint basenecess_base_fk foreign key (mo11_base) references plugins.mobase (mo01_codigo) match full
);

alter table plugins.basenecess owner to plugin;

-- tabela baseescola
CREATE TABLE plugins.baseescola(
  mo02_codigo   serial primary key,
  mo02_base     int4 NOT NULL default 0,
  mo02_escola   int4 NOT NULL default 0,
  mo02_dtcad    date NOT NULL default null,
  mo02_status   bool default 'false'
);

ALTER TABLE plugins.baseescola
  ADD CONSTRAINT baseescola_base_fk FOREIGN KEY (mo02_base) REFERENCES plugins.mobase;

CREATE INDEX baseescola_escola_in ON plugins.baseescola(mo02_escola);
CREATE INDEX baseescola_base_in   ON plugins.baseescola(mo02_base);

alter table plugins.baseescola owner to plugin;

-- tabela baseescturno
CREATE TABLE plugins.baseescturno (
  mo03_codigo     serial primary key,
  mo03_baseescola int4 NOT NULL default 0,
  mo03_turno      int4 NOT NULL default 0,
  mo03_dtcad      date NOT NULL default null,
  mo03_status     bool NOT NULL default 'false',
  mo03_opcao      int4 default 0
);

ALTER TABLE plugins.baseescturno
  ADD CONSTRAINT baseescturno_baseescola_fk FOREIGN KEY (mo03_baseescola) REFERENCES plugins.baseescola;

CREATE INDEX baseescturno_turno_in      ON plugins.baseescturno(mo03_turno);
CREATE INDEX baseescturno_baseescola_in ON plugins.baseescturno(mo03_baseescola);

alter table plugins.baseescturno owner to plugin;

-- tabela alocados
CREATE TABLE plugins.alocados(
  mo13_codigo       serial primary key,
  mo13_base         int4 NOT NULL default 0,
  mo13_fase         int4 NOT NULL default 0,
  mo13_baseescturno int4 default 0
);

ALTER TABLE plugins.alocados
  ADD CONSTRAINT alocados_fase_fk FOREIGN KEY (mo13_fase) REFERENCES plugins.fase,
  ADD CONSTRAINT alocados_baseescturno_fk FOREIGN KEY (mo13_baseescturno) REFERENCES plugins.baseescturno,
  ADD CONSTRAINT alocados_base_fk FOREIGN KEY (mo13_base) REFERENCES plugins.mobase;

CREATE INDEX alocados_baseescturno_in ON plugins.alocados(mo13_baseescturno);
CREATE INDEX alocados_fase_in         ON plugins.alocados(mo13_fase);
CREATE INDEX alocados_base_in         ON plugins.alocados(mo13_base);

alter table plugins.alocados owner to plugin;

-- Tabela criteriosdesignacao
create table plugins.criteriosdesignacao (
  mo16_sequencial serial primary key,
  mo16_descricao  varchar(30)
);

alter table plugins.criteriosdesignacao owner to plugin;

insert into plugins.criteriosdesignacao (mo16_descricao)
  values ('DEFICIÊNCIA'),
         ('BOLSA FAMÍLIA'),
         ('IDADE MAIOR'),
         ('RESPONSÁVEL TRABALHADOR'),
         ('IDADE MENOR'),
         ('REDE DE ORIGEM');

-- tabela criteriosdesignacaoensino
create table plugins.criteriosdesignacaoensino (
  mo17_sequencial           serial primary key,
  mo17_criteriosdesignacao  int not null default 0,
  mo17_ensino               int not null default 0,
  mo17_ordem                int default 0
);

ALTER TABLE plugins.criteriosdesignacaoensino owner to plugin;
alter table plugins.criteriosdesignacaoensino
  add constraint criteriosdesignacaoensino_criteriosdesignacao_fk foreign key (mo17_criteriosdesignacao) references plugins.criteriosdesignacao;

create index criteriosdesignacaoensino_ensino_in              on plugins.criteriosdesignacaoensino(mo17_ensino);
create index criteriosdesignacaoensino_criteriosdesignacao_in on plugins.criteriosdesignacaoensino(mo17_criteriosdesignacao);