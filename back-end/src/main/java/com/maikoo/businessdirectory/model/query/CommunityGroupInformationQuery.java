package com.maikoo.businessdirectory.model.query;

import lombok.Data;

import java.util.Objects;

@Data
public class CommunityGroupInformationQuery extends GroupInformationQuery {
    private String communityName;

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (!(o instanceof CommunityGroupInformationQuery)) return false;
        if (!super.equals(o)) return false;
        CommunityGroupInformationQuery that = (CommunityGroupInformationQuery) o;
        return Objects.equals(getCommunityName(), that.getCommunityName());
    }

    @Override
    public int hashCode() {
        return Objects.hash(super.hashCode(), getCommunityName());
    }
}
