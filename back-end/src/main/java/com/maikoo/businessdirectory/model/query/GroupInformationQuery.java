package com.maikoo.businessdirectory.model.query;

import lombok.Data;

import java.util.Objects;

@Data
public class GroupInformationQuery {
    private long groupId;
    private String name;
    private String avatarUrl;
    private String addrCode;
    private String addrDetail;
    private String brief;

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (!(o instanceof GroupInformationQuery)) return false;
        GroupInformationQuery that = (GroupInformationQuery) o;
        return getGroupId() == that.getGroupId() &&
                Objects.equals(getName(), that.getName()) &&
                Objects.equals(getAvatarUrl(), that.getAvatarUrl()) &&
                Objects.equals(getAddrCode(), that.getAddrCode()) &&
                Objects.equals(getAddrDetail(), that.getAddrDetail()) &&
                Objects.equals(getBrief(), that.getBrief());
    }

    @Override
    public int hashCode() {
        return Objects.hash(getGroupId(), getName(), getAvatarUrl(), getAddrCode(), getAddrDetail(), getBrief());
    }
}
