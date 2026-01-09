<div class="mt-2"><label class="form-label fw-semibold">Add Comment Below</label>
    <div class="border rounded-3 p-2 bg-light" id="chat_container" style="height:200px; overflow-y:auto;"></div>
    <div class="input-group mt-2">
        <input type="text" class="form-control" id="chat_input" placeholder="Tulis pesan...">
        <input type="file" class="form-control" id="chat_file" style="max-width:120px;">
        <a href="javascript:void(0)" class="btn btn-primary" onclick="kirimChat()">
            <i class="fa fa-paper-plane"></i>
        </a>

    </div>
</div>
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-ui/css/jquery-ui.min.css') }}">
<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-ui/js/jquery-ui.min.js') }}"></script>
<script>
    $(document).ready(function() {
        let chatInterval; // simpan interval
        $('#detailTicketModal').on('shown.bs.modal', function () {
            $('#chat_container').empty();
            loadChat(); // langsung load pertama kali
            chatInterval = setInterval(loadChat, 3000); // polling tiap 3 detik
        });
        $('#detailTicketModal').on('hidden.bs.modal', function () {
            clearInterval(chatInterval); // hentikan polling saat modal ditutup
            $('#chat_container').empty();
        });

    });

    function kirimChat() {
        let ticketNo = $('#ticketno').val();
        let message = $('#chat_input').val();
        let fileData = $('#chat_file')[0].files[0];
        console.log("Ticket No :", ticketNo);
        console.log("Message   :", message);
        console.log("File Data :", fileData);
        let formData = new FormData();
        formData.append('ticket_no', ticketNo);
        formData.append('message', message);
        if (fileData) formData.append('file', fileData);

        $.ajax({
            url: '/chat/kirim',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status) {
                    $('#chat_input').val('');
                    $('#chat_file').val('');
                    loadChat();
                } else {
                    alert(res.message);
                }
            },
            error: function() {
                alert('Gagal mengirim pesan');
            }
        });
    }

    function loadChat() {
        let ticketNo = $('#ticketno').val();

        $.ajax({
            url: '/chat/getChats',
            type: 'POST',
            data: {
                ticket_no: ticketNo
            },
            success: function(res) {
                if (!res.status) return;

                let html = '';
                res.data.forEach(chat => {
                    html += `
                    <div style="text-align:${chat.is_me ? 'right' : 'left'}; margin-bottom:6px;">
                        <div class="p-2 rounded"
                             style="display:inline-block;
                                    background:${chat.is_me ? '#d1e7ff' : '#f1f1f1'};
                                    max-width:80%;">
                            <small class="text-muted">${chat.sender_name}</small><br>
                            ${chat.message ?? ''}<br>
                            ${chat.file_path ? `<a href="/storage/${chat.file_path}" target="_blank">📎 File</a>` : ''}
                        </div>
                    </div>
                `;
                });

                $('#chat_container').html(html);
                $('#chat_container').scrollTop($('#chat_container')[0].scrollHeight);
            }
        });
    }
</script>
