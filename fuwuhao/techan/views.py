# -*- coding: utf-8 -*-


from django.http import HttpResponse
from django.template import RequestContext, Template
from django.views.decorators.csrf import csrf_exempt
from django.utils.encoding import smart_str, smart_unicode
from wechatpy.replies import TextReply, ArticlesReply
from wechatpy import parse_message, create_reply
from wechatpy.utils import check_signature
from wechatpy.exceptions import InvalidSignatureException
from wechatpy.replies import BaseReply


TOKEN = 'abdssss'  # 注意要与微信公众帐号平台上填写一致

@csrf_exempt
def wechat_home(request):
    print "x"
    if request.method == 'GET':
        signature = request.GET.get('signature', '')
        timestamp = request.GET.get('timestamp', '')
        nonce = request.GET.get('nonce', '')
        echo_str = request.GET.get('echostr', '')
        try:
            check_signature(TOKEN, signature, timestamp, nonce)
        except InvalidSignatureException:
            echo_str = 'error'
        response = HttpResponse(echo_str, content_type="text/plain")
        return response
    else:
        reply=None
        msg = parse_message(request.body)
        if msg.type == "text":
            reply = TextReply(content='欢迎关注RYHAN的微信订阅号，回复XX天气即可查询天气情况。\r\n 如：合肥天气', message=msg)
            return HttpResponse(reply.render() , content_type="application/xml")
