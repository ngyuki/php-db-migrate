
drop table if exists db_migrate_v3;


drop table if exists db_migrate;

create table db_migrate (
    version varchar (255) not null,
    apply_at datetime not null,
    primary key (version)
);


insert into db_migrate values ('20140828-01.sql', now());

drop table if exists tt;

create table tt (
  id int not null primary key
);
