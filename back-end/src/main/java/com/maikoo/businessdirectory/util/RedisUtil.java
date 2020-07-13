package com.maikoo.businessdirectory.util;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import lombok.extern.slf4j.Slf4j;
import org.apache.commons.lang3.RandomStringUtils;
import org.springframework.data.redis.core.RedisTemplate;
import org.springframework.stereotype.Component;
import org.springframework.util.Assert;

import javax.annotation.Resource;
import java.security.InvalidParameterException;

@Slf4j
@Component
public class RedisUtil {
    @Resource
    private RedisTemplate<String, Object> redisTemplate;

    public void updateValue(String key, Object value) {
        Assert.notNull(redisTemplate.hasKey(key), "key不能为空");

        Long expire = redisTemplate.getExpire(key);

        redisTemplate.delete(key);

        if (expire == -1) {
            redisTemplate.opsForValue().set(key, value);
        } else {
            redisTemplate.opsForValue().set(key, value, expire);
        }
    }

    public String groupSN(GroupTypeEnum groupType, long userId) {
        StringBuilder key = new StringBuilder();
        key.append(groupType);
        key.append("_");
        key.append(userId);
        key.append("_");
        key.append(System.currentTimeMillis() / 1000);
        boolean isExited = true;
        do {
            key.append(RandomStringUtils.random(3, false, true));
            if (!redisTemplate.hasKey(key.toString())) {
                isExited = false;
            } else {
                key.setLength(key.toString().lastIndexOf("_") + 1 + 10);
            }
        } while (isExited);
        return key.toString();
    }

    /**
     * 获取值同时判断是否包含过期时间
     *
     * @param key
     * @return
     */
    public Object value(String key) {
        if (!redisTemplate.hasKey(key)) {
            log.info("群不存在。key: {}", key);
            throw new InvalidParameterException();
        }

        long expire = redisTemplate.getExpire(key);

        if (expire != -1 && expire <= 0) {
            log.info("群已过期。key: {}", key);
            throw new InvalidParameterException();
        }

        return redisTemplate.opsForValue().get(key);
    }

    public void delete(String key) {
        if (redisTemplate.hasKey(key)) {
            redisTemplate.delete(key);
        }
    }
}
