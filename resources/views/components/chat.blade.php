<div class="mt-2"><label class="form-label fw-semibold">Add Comment Below</label>
    <div class="border rounded-3 p-2 bg-light" id="chat_container" style="height:200px; overflow-y:auto;"></div>
    <div class="input-group mt-2">
        <input type="text" class="form-control" id="chat_input" placeholder="Tulis pesan...">
        <input type="file" class="form-control" id="chat_file" style="max-width:120px;">
        <button class="btn btn-primary" id="btnSendChat"><i class="fa fa-paper-plane"></i></button>
    </div>
</div>

<script>
    $(document).ready(function() {




        // setInterval(loadChats, 3000);
        // loadChats();
    });

    function loadChats(ticketNo) {
        console.log(ticketNo);
        // pakai ticketNo langsung

        // $.ajax({
        //     url: '/chat/get-messages',
        //     type: 'GET',
        //     data: {
        //         ticket_no: ticketNo
        //     },
        //     success: function(res) {
        //         let html = '';
        //         res.forEach(chat => {
        //             let align = chat.pengirim_username ===
        //                 "{{ Auth::user()->username }}" ? 'text-end' : 'text-start';
        //             let bg = chat.pengirim_username === "{{ Auth::user()->username }}" ?
        //                 'bg-primary text-white' : 'bg-light';
        //             html += `<div class="mb-1 ${align}">
        //                     <div class="d-inline-block p-2 rounded-3 ${bg}" style="max-width: 80%;">
        //                         <small class="text-muted">${chat.pengirim_username}</small><br>
        //                         ${chat.pesan}
        //                         ${chat.file_path ? `<br><a href='/${chat.file_path}' target='_blank'>Lampiran</a>` : ''}
        //                     </div>
        //                  </div>`;
        //         });
        //         $('#chat_container_' + ticketNo).html(html);
        //         $('#chat_container_' + ticketNo).scrollTop($('#chat_container_' + ticketNo)[0]
        //             .scrollHeight);
        //     }
        // });
    }

    // $('.btnSendChat_' + ticketNo).click(function() {
    //     let pesan = $('.chat_input_' + ticketNo).val();
    //     let fileData = $('.chat_file_' + ticketNo)[0].files[0];
    //     if (!pesan && !fileData) return alert('Tulis pesan atau pilih file');

    //     let formData = new FormData();
    //     formData.append('no_ticket', ticketNo);
    //     formData.append('pesan', pesan);
    //     if (fileData) formData.append('file', fileData);

    //     $.ajax({
    //         url: '/chat/send-message',
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //         },
    //         success: function(res) {
    //             $('.chat_input_' + ticketNo).val('');
    //             $('.chat_file_' + ticketNo).val('');
    //             loadChats();
    //         }
    //     });
    // });
</script>
