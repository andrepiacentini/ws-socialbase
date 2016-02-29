# simple microblog
webservice REST simples, para emular um microblog

##### instruções para instalação:
* instalar o Composer 
* rodar no terminal `php composer.phar install`
* criar uma base de dados MySQL
* rodar o script em /data/microblog.sql
* configurar o arquivo config.php para o nome do database e configurar o arquivo local.php para usuário e senha de conexão
* **enjoy!**

## Webservice REST Microblog
### URI: /Microblog

**getAllPosts**
retorna um JSON contendo uma coleção de posts, incluindo os posts excluídos e inativos

**getAllActivePosts**
retorna um JSON contendo uma coleção de posts, somente com posts ativos e válidos

**getAllActivePostsReverse**
retorna um JSON contendo uma coleção de posts, somente com posts ativos e válidos, em ordem decrescente de data de postagem

**getLastActivePosts**
`post param: { "last_post_id" : POST_ID }`
retorna um JSON contendo uma coleção de posts, somente com posts ativos e válidos, a partir do ID de post informado

**createPost**
`post param: { "message" : TEXT }`
cria um post com o conteúdo passado. Tags HTML são ignoradas.

**deletePost**
`post param: { "post_id" : POST_ID }`
marca um post como excluído.

