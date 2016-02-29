            function catchJson(json) {
                $('.loading').hide();
                if (json.posts) {
                    if (iLastPostId!=null) {
                        $('.posts').html('');
                    }
                    for (i=0;i<json.posts.length;i++) {
                        insertHTMLPost(json.posts[i]);
                        iLastPostId = json.posts[i].id;
                    }
                }
                setAutomaticRefrest(iLastPostId);
            }
            
            
            function postar() {
                loading();
                var content = $('.post-content').val();
                $('.post-content').val('');
                $('#remain').text('140');
                WSRestPost('/Microblog/createPost', '{ "message" : "'+content+'" }', catchJsonPost, 5000, false);
            }
            
            
            function catchJsonPost(json) {
                if (json.status=='200') {
                    $('.loading').hide();
                    insertHTMLPost(json.post_data[0]);
                    iLastPostId = json.post_data[0].id;
                    setAutomaticRefrest(iLastPostId);
                }
            }
            
            function insertHTMLPost(post) {
                console.log('Mostrando post: '+JSON.stringify(post));
                var sHTML = '<div class="panel panel-default"><div class="panel-body"><div class="col-md-2"><img src="img/anonymous.png"></div><div class="col-md-10 text-left"><p><strong>Usuário anônimo</strong> em '+post.human_date+'</p><br/><p>'+post.content+'</p></div></div></div>';
                $('.posts').prepend(sHTML);
            }
          
            
            function loading() {
                $('.loading').show();
            }
            
            
            function setAutomaticRefrest(iLastPostId) {
                console.log('Último ID: '+iLastPostId);
                clearTimeout(timeObj);
                timeObj = setTimeout(function() {
                    persistentLoad(iLastPostId);
                },iTimeLimitReload*1000);
            }
            
            
            /* Captura os últimos post, até o último ID mostrado na tela, após o intervalo de tempo, de forma automática */
            function persistentLoad(iLastPostId) {
                WSRestPost('/Microblog/getLastActivePosts', '{ "last_post_id": '+iLastPostId+' }', catchJson, 5000, false);
            }
