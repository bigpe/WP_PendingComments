var completeData = [];

function addNewComment(node, answer = false, id = 0) {
    let pendingCommentsComplete = document.getElementById("pendingCommentsComplete");
    let newCommentBlock = document.createElement("div");
    let commentType;
    let parentId = 0;
    let label;
    id++;
    newCommentBlock.setAttribute("id", id.toString());
    if(answer) {
        parentId = node.parentElement.id;
        commentType = "Answer";
        newCommentBlock.setAttribute("class", "answers");
        label = 'Ответ #' + id + ' к комментарию #' + parentId;
    }
    if(!answer) {
        commentType = "Comment";
        newCommentBlock.setAttribute("class", "comments");
        label = 'Комментарий #' + id;
    }
    newCommentBlock.innerHTML = "<label>" + label + "</label>" +
        "<input type='text' placeholder='Имя пользователя' " +
        "oninput='updateCompleteData(this, " + id + ", \"Author\", " + parentId  + ")'>" +
        "<input type='date' placeholder='Дата' " +
        "onchange='updateCompleteData(this, " + id + ", \"Date\", " + parentId  + ")'>" +
        "<input type='time' onchange='updateCompleteData(this, " + id + ", \"Time\", " + parentId  + ")'>" +
        "<input type='text' onchange='updateCompleteData(this, " + id + ", \"Text\", " + parentId  + ")' " +
        "placeholder='Текст сообщения'>";
    if(!answer) {
        newCommentBlock.innerHTML += "<a href='#answer-add' onclick='addNewComment(this, true)' " +
            "class='hide-if-no-js taxonomy-add-new'>+ Добавить ответ</a><hr>";
        completeData.push({'CommentID': id, 'CommentType': commentType, 'Answers': []});
    }
    if(answer)
        completeData[(parentId - 1)].Answers.push({'CommentID': id, 'CommentType': commentType});
    node.onclick = function () {
        addNewComment(this, answer, id);
    };
    node.after(node, newCommentBlock);
    pendingCommentsComplete.value = JSON.stringify(completeData);
}

function updateCompleteData(node, id, action, parentId = 0) {
    let pendingCommentsComplete = document.getElementById("pendingCommentsComplete");
    let value = node.value;
    if(!parentId)
        completeData[id-1][action] = value;
    else
        completeData[parentId-1]['Answers'][id-1][action] = value;
    pendingCommentsComplete.value = JSON.stringify(completeData);
}