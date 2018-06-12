<style type="text/css">
    .wrap {height: 100%; }
    .chat { 
        height: 40vh;
        overflow-y: auto}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.15.3/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.2.1/vue.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/3.7.0/firebase.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>

<script>

  // Initialize Firebase
    //$user means login users
    var user = {!! $user !!};
    console.log(user);
    
    //It's demo credentials if you want to new config check https://console.firebase.google.com 
    
    var config = {
        apiKey: "AIzaSyCUp1xkrl0u7yxcJKj8avFvciBtbXRngwU",
        authDomain: "my-firebase-b7a0b.firebaseapp.com",
        databaseURL: "https://my-firebase-b7a0b.firebaseio.com",
        storageBucket: "my-firebase-b7a0b.appspot.com",
        messagingSenderId: "381134740430"
    };

    
    firebase.initializeApp(config);

    var database = firebase.database().ref('chats');//.push();

    var storageRoom = firebase.storage();
    
    function writeData(text) {
      database.push({
        text: text,
        timestamp: Date.now(),
        user: user
      });
    };

    function writeFile(file) {
      var file_type =file.type.split('/'); 

      // Upload the image to Cloud Storage.
      var filePath = Date.now()+file.name;
      storageRoom.ref(filePath).put(file).then(function(snapshot) {        

        // Get the file's Storage URI and update the chat message placeholder.
        var fullPath = snapshot.metadata.downloadURLs[0];
        database.push({
            url: fullPath,
            type: file_type[0],
            timestamp: Date.now(),
            user: user,
          })
        
        }.bind(this)).catch(function(error) {
          console.error('There was an error uploading a file to Cloud Storage:', error);
        });
    };
    
    function toBottom() {
        document.querySelector('.chat').scrollTop = 1000;
    }
    
    new Vue({
        el: '#app',
        data: {
            chatMessages: [],
            txt: '',
            file:''
        },
        mounted: function() {
            var self = this;
            var now = Date.now();
            database.on('child_added', function(data) {
                $('#loader_id').hide();
                console.log(data.key, data.val(), moment(data.timestamp).fromNow());
                console.log(now, data.val().timestamp)
                if (now < data.val().timestamp) {
                    self.chatMessages.push(data.val());
                    // document.querySelector('.chat').scrollTop = 1000;
                    // self.$el.querySelector('.chat').scrollTop = 
                }
              setTimeout(function() {
                toBottom();
              }, 0)
            });
        },
        template: `
            <div class="wrap">
                <div class="row chat">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="media" v-for="chat in chatMessages">
                          <div class="media-left">
                            <a href="#">
                              <img class="media-object" v-bind:src="chat.user.image" alt="...">
                            </a>
                          </div>
                          <div class="media-body">
                            <div>
                                <span class="label label-primary">@{{chat.user.email}}:</span>
                                <span class="label label-info">@{{moment(chat.timestamp).fromNow()}}</span>
                            </div>
                            <h4>@{{chat.text}}</h4>
                             <img v-bind:src="chat.url">
                          </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-sm-offset-3">
                        <form @submit.prevent="send()" enctype="multipart/form-data" >
                            <div class="input-group">
                                <input type="file" name="myfile" v-model="file" @change="sendfile" />
                              <input type="text" class="form-control" placeholder="Type here..." v-model="txt">
                              <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">Send</button>
                              </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `,
        methods: {
            send: function() {
                console.log('send');
                let txt = this.txt.trim();
                console.log(this.txt.trim());
                if (txt) {
                    writeData(txt);
                    this.txt = '';
                }
            },
            sendfile: function(e) {
                $('#loader_id').show();
                var file = e.target.files || e.dataTransfer.files;
                console.log(file[0]);                
                if (file[0]) {
                    writeFile(file[0]);
                    this.file = '';
                }
            }
        }
    })
</script>
