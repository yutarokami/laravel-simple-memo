function deleteHandle(event){
    // 一旦フォームをストップ
    event.preventDefault();
    if(window.confirm('本当に削除していいですか？')) {
        // 削除OKならフォームを再開
        document.getElementById('delete-form').submit();
    }else{
        alert('キャンセルしました');
    }
}