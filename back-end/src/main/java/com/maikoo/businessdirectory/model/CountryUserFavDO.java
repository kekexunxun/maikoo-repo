package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class CountryUserFavDO {
    private long idx;
    private long userId;
    private CountryUserDO countryUserDO;
    private long createdAt;
}
