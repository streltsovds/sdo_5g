$(document).ready(function(){
    $(document).on('click', '.cancel-upload > a', function(){
        var li = $(this).parent().parent();
        $.get($(this).attr('href'), function(data){
//#14979 //������� ������ �� ����������������� ��� ��������
// ����������� �������� �������� ������ ��� ��������� - ��������������� �����������������
            var id = li.parent().attr('id');    // ��������� ������������� 
            id = id.substring(0, id.length-5);  // �������� ��������
            $('#'+id).data('fileupload')._trigger('destroy');
//
            li.remove();
        });
        return false;
    });
});