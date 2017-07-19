create table tt (
  id int not null primary key
);

insert into tt values (1000);
insert into tt values (1001);

/* {{ down }}

drop table tt;

/**/
