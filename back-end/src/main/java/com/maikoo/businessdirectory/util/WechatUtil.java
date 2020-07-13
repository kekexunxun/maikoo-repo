package com.maikoo.businessdirectory.util;

import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.google.common.io.ByteStreams;
import com.maikoo.businessdirectory.config.CustomEnvironmentConfig;
import com.maikoo.businessdirectory.exception.SystemException;
import com.maikoo.businessdirectory.exception.WechatException;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.constant.ReviewStatusEnum;
import com.maikoo.businessdirectory.model.dto.BaseMessageDTO;
import com.maikoo.businessdirectory.model.dto.MessageDataDTO;
import com.maikoo.businessdirectory.model.dto.MiniProgramMessageDTO;
import com.maikoo.businessdirectory.model.query.PhoneDecryptQuery;
import lombok.extern.slf4j.Slf4j;
import org.apache.http.client.utils.URIBuilder;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Component;

import javax.crypto.Cipher;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;
import javax.imageio.ImageIO;
import javax.servlet.http.HttpSession;
import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.Reader;
import java.net.URI;
import java.net.URISyntaxException;
import java.security.InvalidParameterException;
import java.security.Security;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.TimeUnit;

@Slf4j
@Component
public class WechatUtil {
    @Autowired
    private RedisTemplate<String, String> redisTemplate;
    @Autowired
    private CustomEnvironmentConfig customEnvironmentConfig;
    @Autowired
    private HttpSession session;
    @Autowired
    private ObjectMapper objectMapper;

    public String phoneEncrypt(PhoneDecryptQuery phoneDecryptQuery) {
        String phone = null;

        UserDO currentUserDO = (UserDO) session.getAttribute("current_user");

        String key = currentUserDO.getSessionKey();
        String iv = phoneDecryptQuery.getIv();

        byte[] keyBytes = Base64.getDecoder().decode(key);
        byte[] ivBytes = Base64.getDecoder().decode(iv);
        byte[] encryptedDataBytes = Base64.getDecoder().decode(phoneDecryptQuery.getEncryptedData());

        log.info("微信敏感数据。key:{},iv:{},data:{}", key, iv, phoneDecryptQuery.getEncryptedData());

        try {
            Security.addProvider(new BouncyCastleProvider());
            SecretKeySpec secretKeySpec = new SecretKeySpec(keyBytes, "AES");
            IvParameterSpec ivParameterSpec = new IvParameterSpec(ivBytes);

            Cipher cipher = Cipher.getInstance("AES/CBC/PKCS7Padding");
            cipher.init(Cipher.DECRYPT_MODE, secretKeySpec, ivParameterSpec);
            byte[] originalBytes = cipher.doFinal(encryptedDataBytes);

            JsonNode jsonNode = objectMapper.readTree(originalBytes);

            if (!jsonNode.get("watermark").get("appid").asText().equals(customEnvironmentConfig.getMiniAppId())) {
                throw new InvalidParameterException();
            }

            phone = jsonNode.get("purePhoneNumber").asText();
        } catch (Exception e) {
            log.error("解密微信敏感数据失败。key:{},iv:{},data:{}", key, iv, phoneDecryptQuery.getEncryptedData());
            throw new WechatException("解密微信敏感数据失败", e);
        }
        return phone;
    }

    public void accessToken() {
        URI uri = null;
        try {
            uri = new URIBuilder()
                    .setScheme("https")
                    .setHost("api.weixin.qq.com")
                    .setPath("/cgi-bin/token")
                    .setParameter("appid", customEnvironmentConfig.getMiniAppId())
                    .setParameter("secret", customEnvironmentConfig.getMiniAppSecret())
                    .setParameter("grant_type", "client_credential")
                    .build();
        } catch (URISyntaxException e) {
            throw new SystemException("构建URL错误", e);
        }

        String accessToken = RequestUtil.get(uri, content -> {
            Reader reader = new InputStreamReader(content);

            JsonNode jsonNode = stringToJsonNode(reader);

            checkWeChatRequestError(jsonNode);

            return jsonNode.get("access_token").asText();
        });

        redisTemplate.opsForValue().set("wechat_access_token", accessToken, 6600, TimeUnit.SECONDS);
    }

    public UserDO login(String code) {
        URI uri = null;
        try {
            uri = new URIBuilder()
                    .setScheme("https")
                    .setHost("api.weixin.qq.com")
                    .setPath("/sns/jscode2session")
                    .setParameter("appid", customEnvironmentConfig.getMiniAppId())
                    .setParameter("secret", customEnvironmentConfig.getMiniAppSecret())
                    .setParameter("js_code", code)
                    .setParameter("grant_type", "authorization_code")
                    .build();
        } catch (URISyntaxException e) {
            throw new SystemException("构建URL错误", e);
        }

        return RequestUtil.get(uri, content -> {
            UserDO userDO = null;

            Reader reader = new InputStreamReader(content);
            JsonNode jsonNode = stringToJsonNode(reader);

            checkWeChatRequestError(jsonNode);

            String openid = jsonNode.get("openid").asText();
            String sessionKey = jsonNode.get("session_key").asText();
            String unionId = jsonNode.has("unionid") ? jsonNode.get("unionid").asText() : null;

            userDO = new UserDO();
            userDO.setOpenid(openid);
            userDO.setSessionKey(sessionKey);
            userDO.setUnionId(unionId);
            return userDO;
        });
    }

    /**
     * 小程序模板消息
     *
     * @param miniProgramMessageDTO 小程序模版消息请求参数
     * @return 发送成功后，返回true；发送失败时如果是formid不可用问题，则返回false，否则抛出WechatException
     */
    public boolean miniProgramMessage(MiniProgramMessageDTO miniProgramMessageDTO) {
        boolean result = true;
        String accessToken = redisTemplate.opsForValue().get("wechat_access_token");
        URI uri = null;

        try {
            uri = new URIBuilder()
                    .setScheme("https")
                    .setHost("api.weixin.qq.com")
                    .setPath("/cgi-bin/message/wxopen/template/send")
                    .setParameter("access_token", accessToken)
                    .build();
        } catch (URISyntaxException e) {
            throw new SystemException("构建URL错误", e);
        }

        try {
            return RequestUtil.postByJson(uri, objectMapper.writeValueAsString(miniProgramMessageDTO), content -> {
                Reader reader = new InputStreamReader(content);

                JsonNode jsonNode = stringToJsonNode(reader);

                JsonNode errcode = jsonNode.get("errcode");
                int errcodeInteger = errcode.asInt();
                log.info("error code: {}", errcodeInteger);
                if (errcodeInteger != 0 && errcodeInteger != 41028 && errcodeInteger != 41029) {
                    throw new WechatException("errorCode:" + jsonNode.get("errcode").asInt() + "\tmessage:" + jsonNode.get("errmsg").asText());
                } else if (errcodeInteger == 41028 || errcodeInteger == 41029) {
                    return false;
                }

                return true;
            });
        } catch (JsonProcessingException e) {
            throw new SystemException("构建JSON错误", e);
        }
    }

    /**
     * 公众号模板消息
     *
     * @param baseMessageDTO 公众号、小程序通用模版消息请求参数
     */
    public void officialMessage(BaseMessageDTO baseMessageDTO) {
        String accessToken = redisTemplate.opsForValue().get("wechat_access_token");
        URI uri = null;

        try {
            uri = new URIBuilder()
                    .setScheme("https")
                    .setHost("api.weixin.qq.com")
                    .setPath("/cgi-bin/message/wxopen/template/uniform_send")
                    .setParameter("access_token", accessToken)
                    .build();
        } catch (URISyntaxException e) {
            throw new SystemException("构建URL错误", e);
        }

        try {
            RequestUtil.postByJson(uri, objectMapper.writeValueAsString(baseMessageDTO), content -> {
                Reader reader = new InputStreamReader(content);

                JsonNode jsonNode = stringToJsonNode(reader);
                JsonNode errorCodeJsonCode = jsonNode.get("errcode");

                int errorCodeInt = errorCodeJsonCode.asInt();
                if (errorCodeInt != 0) {
                    throw new WechatException("error code:" + jsonNode.get("errcode").asInt() + "\tmessage:" + jsonNode.get("errmsg").asText());
                }

                return "SUCCESS";
            });
        } catch (JsonProcessingException e) {
            throw new SystemException("构建JSON错误", e);
        }
    }

    public BufferedImage qrCode(GroupTypeEnum groupType, long groupId) {
        String accessToken = redisTemplate.opsForValue().get("wechat_access_token");
        int type;

        switch (groupType) {
            case COUNTRY:
                type = 0;
                break;
            case SCHOOL:
                type = 1;
                break;
            case CLASS:
                type = 2;
                break;
            case COMMUNITY:
                type = 3;
                break;
            default:
                throw new InvalidParameterException("无效群类型（" + groupType + "）");
        }

        URI uri = null;

        try {
            uri = new URIBuilder()
                    .setScheme("https")
                    .setHost("api.weixin.qq.com")
                    .setPath("/wxa/getwxacodeunlimit")
                    .setParameter("access_token", accessToken)
                    .build();


        } catch (URISyntaxException e) {
            throw new SystemException("构建URL错误", e);
        }

        Map<String, String> data = new HashMap<>();
        data.put("scene", "gtype=" + type + "&gid=" + groupId + "&action=into");
        data.put("page", "pages/share/share");

        try {
            return RequestUtil.postByJson(uri, objectMapper.writeValueAsString(data), content -> {
                byte[] bytes = ByteStreams.toByteArray(content);

                String resultString = new String(bytes);

                // 请求失败，会返回 JSON 格式的数据；调用成功，会直接返回图片二进制内容
                JsonNode jsonNode = null;
                try {
                    jsonNode = objectMapper.readTree(resultString);
                } catch (Exception e) {
                }

                if (jsonNode != null) {
                    throw new WechatException("error code:" + jsonNode.get("errcode").asInt() + "\tmessage:" + jsonNode.get("errmsg").asText());
                }

                return ImageIO.read(new ByteArrayInputStream(bytes));
            });
        } catch (JsonProcessingException e) {
            throw new SystemException("构建JSON错误", e);
        }
    }

    /**
     * 小程序模版消息数据，加入社区成功
     *
     * @param groupName  群名称
     * @param dateTime   加入时间
     * @param brief      群简介
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> miniCommunityApplySuccessData(String groupName, String dateTime, String brief) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("keyword1", MessageDataDTO.value(groupName));
        result.put("keyword2", MessageDataDTO.value(dateTime));
//        result.put("keyword3", MessageDataDTO.value(processedName));
        result.put("keyword3", MessageDataDTO.value(brief));
        result.put("keyword4", MessageDataDTO.value("欢迎加入"+groupName+"群"));
        return result;
    }

    /**
     * 小程序模板消息数据，申请结果
     *
     * @param dateTime     申请时间
     * @param reviewStatus 审核状态
     * @param groupName    群名称
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> miniApplyResultData(String dateTime, ReviewStatusEnum reviewStatus, String groupName) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("keyword1", MessageDataDTO.value(dateTime));
        result.put("keyword2", MessageDataDTO.value(reviewStatus.getStringStatus()));
        result.put("keyword3", MessageDataDTO.value(reviewStatus.equals(ReviewStatusEnum.APPROVE) ? "欢迎您加入群「" + groupName + "」" : "你的申请已被驳回，点击查看详情"));
        return result;
    }

    /**
     * 小程序模版消息，群主变更
     *
     * @param groupName 群名称
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> miniChangeOwnerData(String groupName) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("keyword1", MessageDataDTO.value(groupName));
        result.put("keyword2", MessageDataDTO.value("你已经变更为新的群主"));
        return result;
    }

    /**
     * 公众号模版消息，审核通知
     *
     * @param groupName  群名称
     * @param name       申请人名称
     * @param dateTime   申请时间
     * @param reviewSize 审核总条数
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> officialReviewData(String groupName, String name, String dateTime, int reviewSize) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("first", MessageDataDTO.value("有新的" + groupName + "群申请啦"));
        result.put("keyword1", MessageDataDTO.value(name));
        result.put("keyword2", MessageDataDTO.value(dateTime));
        result.put("keyword3", MessageDataDTO.value(groupName));
        result.put("remark", MessageDataDTO.value("您有" + reviewSize + "条审核需要处理，请点击处理，请主动发送任意一条消息给我们以便您能收到我们的通知"));
        return result;
    }

    /**
     * 公众号模版消息，解散群
     *
     * @param groupName 群名称
     * @param dateTime  解散时间
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> officialDismissData(String groupName, String dateTime) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("first", MessageDataDTO.value("有所在的群组\"" + groupName + "\"已经解散"));
        result.put("keyword1", MessageDataDTO.value("管理员解散"));
        result.put("keyword2", MessageDataDTO.value(dateTime));
        result.put("remark", MessageDataDTO.value("群组\"" + groupName + "\"成功解散"));
        return result;
    }

    /**
     * 公众号模版消息，群组变更
     *
     * @param groupName 群名称
     * @param ownerName 变更后用户名称
     * @return 对应模版消息数据
     */
    public Map<String, MessageDataDTO> officialChangeOwnerData(String groupName, String ownerName) {
        Map<String, MessageDataDTO> result = new HashMap<>();
        result.put("first", MessageDataDTO.value(groupName + "群群主转让成功"));
        result.put("keyword1", MessageDataDTO.value("将" + groupName + "群转让给" + ownerName));
        result.put("keyword2", MessageDataDTO.value("通过"));
        result.put("remark", MessageDataDTO.value("欢迎您及时关注和查看该群的更多信息"));
        return result;
    }

    private JsonNode stringToJsonNode(Reader reader) throws IOException {
        return objectMapper.readTree(reader);
    }

    private void checkWeChatRequestError(JsonNode responseJsonNode) {
        if (responseJsonNode.has("errcode")) {
            throw new WechatException("error code:" + responseJsonNode.get("errcode").asInt() + "\tmessage:" + responseJsonNode.get("errmsg").asText());
        }
    }
}
