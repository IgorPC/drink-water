<h1 align="center">API REST - DRINK WATER</h1>
<h2 align="center">API desenvolvida para a vaga BACKEND PHP</h2>
<hr>
<p>
    Nesse documento deixarei as instruções de como utilizar a API bem como as tecnologias utilizadas e o passo a passo
    para replicar o banco de dados:
</p>
<hr>
<h3>Sobre as Tecnologias Utilizadas: </h3>
<p>
    Para esse projeto não foi utilizado nenhum Framework para manipulação de rotas, banco de dados ou qualquer outra integração.
</p>
<p>
    A API foi desenvolvida utilizando o PHP, visando desempenho e otimização do código foi utilizada as dependências da
    biblioteca Route para tratamento de rotas e a Laminas para respostas. O JWT Token foi implementado utilizando a biblioteca
    do Lcobucci e por fim, foi utilizado o MySQL como banco de dados relacional.
</p>
<h3>Rotas: </h3>
<ul>
    <li> <strong>(POST)</strong> /users/ => Cria um novo usuário;</li>
    <li> <strong>(POST)</strong> /login => Efetua o login;</li>
    <li> <strong>(GET)*</strong> /users/ => Retorna todos os usuários cadastrados;</li>
    <li> <strong>(GET)*</strong> /users/{id} => Retorna um usuário especifico;</li>
    <li> <strong>(PUT)*</strong> /users/{id} => Altera os dados do usuário autenticado;</li>
    <li> <strong>(DELETE)*</strong> /users/{id} => Remove o usuário autenticado;</li>
    <li> <strong>(POST)*</strong> /users/{id}/drink => Atualiza o contador de bebidas do usuário autenticado e insere os dados no histórico;</li>
    <li> <strong>(GET)*</strong> /users/{id}/drink/historic => Retorna o histórico de bebidas de um usuário especifico;</li>
    <li> <strong>(GET)*</strong> /users/drink/rank => Retorna o usuário que mais bebeu naquele dia;</li>
</ul>
<p>
    As rotas que possuem um asterisco após o seu método são protegidas pela autenticação com Token.
</p>
<h3>Código para replicar o banco de dados:</h3>
<p>
    create database processo_seletivo default charset=utf8mb4;
</p>
<p>
    create table users(id int auto_increment primary key, name varchar(30), email varchar(30), password varchar(30), drink_counter int);
</p>
<p>
    create table drink_history(id int auto_increment primary key, user_id int, ml int, data datetime);
</p>
<p>
    ALTER TABLE `drink_history` ADD CONSTRAINT `fk_id` FOREIGN KEY ( `user_id` ) REFERENCES `users` ( `id` ) ;
</p>
