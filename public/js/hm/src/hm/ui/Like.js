HM.define('hm.ui.Like', {

    extend: 'hm.ui.Component',

    config: {
        elTag: 'span',
        likeCount: 0,
        dislikeCount: 0,
        itemType: 0,
        itemId: 0
    },

    // ==========================================================================
    //
    //                              Инициализация
    //
    // ==========================================================================


    /**
     * Инициализация
     *
     * @memberOf hm.ui.RoleSwitcher
     */
    _init: function() {

        this.callParent(arguments);

        var d = document;
        
        this.$buttonLike    = $(d.createElement('a'));
        this.$buttonDislike = $(d.createElement('a'));
        this.$likeCount     = $(d.createElement('span'));
        this.$dislikeCount  = $(d.createElement('span'));
        
        this.setLikeCount(this.likeCount);
        this.setDislikeCount(this.dislikeCount);
        this.setVote(this.vote);

        this.$buttonLike.addClass('hm-like-button-like');
        this.$buttonDislike.addClass('hm-like-button-dislike');
        
        this.$likeImage    = $(d.createElement('div'));
        this.$disLikeImage = $(d.createElement('div'));

        this.$likeImage.addClass('hm-like-button-like-image');
        this.$disLikeImage.addClass('hm-like-button-dislike-image');
        
        this.$buttonLike.append(this.$likeImage);
        this.$buttonLike.append(this.$likeCount);
        this.$buttonDislike.append(this.$disLikeImage);
        this.$buttonDislike.append(this.$dislikeCount);
        
        this.$el.append(this.$buttonLike);
        this.$el.append(this.$buttonDislike);

        this.$el.addClass('hm-like');

        this.$buttonLike.on('click', _.bind(this.onClick_like, this));
        this.$buttonDislike.on('click', _.bind(this.onClick_dislike, this));
    },
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // ==============================================================================
    //
    //                             Методы класса
    //
    // ==============================================================================

    setLikeCount: function(value) {
        this.$likeCount.text(value);
        this.likeCount = value;
    },

    setDislikeCount: function(value) {
        this.$dislikeCount.text(value);
        this.dislikeCount = value;
    },
    
    TYPE_LIKE: 'LIKE',
    TYPE_DISLIKE: 'DISLIKE',
    
    sendLike: function(type) {
        
        this.locked = true;
        
        var params = {
            like_type: type,
            item_type: this.itemType,
            item_id: this.itemId
        };
        
        $.ajax({
            type: 'post',
            url: '/like/index/like',
            data: params,
            success:  _.bind(this.onSendLikeSuccess, this),
            error:    _.bind(this.onSendLikeFailure, this)
        })
    },

    setVote: function(vote) {
        this.vote = vote;

        this.$el.removeClass('hm-like-liked');
        this.$el.removeClass('hm-like-disliked');

        switch (vote) {
            case -1:
                this.$el.addClass('hm-like-disliked');
                break;
            case 1:
                this.$el.addClass('hm-like-liked');
                break;
        }
    },
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // ==============================================================================
    //
    //                             Обработчики событий
    //
    // ==============================================================================
    
    onClick_like: function() {
        if (this.locked) {
            return;
        }

        if (this.vote == -1) {
            this.setDislikeCount(this.dislikeCount - 1);
        }
        
        if (this.vote == 1) {
            this.setVote(0);
            this.setLikeCount(this.likeCount - 1);
        } else {
            this.setVote(1);
            this.setLikeCount(this.likeCount + 1);
        }
        
        
        this.sendLike(this.TYPE_LIKE);
        
    },

    onClick_dislike: function() {
        if (this.locked) {
            return;
        }
        if (this.vote == 1) {
            this.setLikeCount(this.likeCount - 1);
        }
        
        if (this.vote == -1) {
            this.setVote(0);
            this.setDislikeCount(this.dislikeCount - 1);
        } else {
            this.setVote(-1);
            this.setDislikeCount(this.dislikeCount + 1);
        }
        
        
        this.sendLike(this.TYPE_DISLIKE);

    },

    onSendLikeSuccess: function(result) {
        result = $.parseJSON(result);
        
        if (result.message === 'OK') {
            result = result.result;
            
            this.setLikeCount(result.count_like);
            this.setDislikeCount(result.count_dislike);
        }
        this.locked = false;
    },

    onSendLikeFailure: function() {
        alert(result.message);
        this.locked = false;
    }
});