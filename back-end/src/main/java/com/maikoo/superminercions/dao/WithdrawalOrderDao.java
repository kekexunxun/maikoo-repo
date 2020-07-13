package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.WithdrawalOrderDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

@Mapper
public interface WithdrawalOrderDao {
    @Insert("INSERT " +
            "INTO " +
                "c_order_withdrawal(c_order_id, quantity, price, fee, method) " +
            "VALUES" +
                "(#{id}, #{quantity}, #{price}, #{fee}, #{method})")
    int insert(WithdrawalOrderDO withdrawalOrderDO);

    @Select("SELECT " +
                "cow.id " +
            "FROM " +
                "c_order co " +
                "JOIN c_order_withdrawal cow ON co.id = cow.c_order_id " +
                "AND co.deleted_at IS NULL " +
            "WHERE " +
                "co.c_user_id = #{customerUserId}")
    List<Long> selectPageIds(@Param("customerUserId") long customerUserId);

    WithdrawalOrderDO selectOne(long orderSN);

    List<WithdrawalOrderDO> selectByIds(@Param("ids") List<Long> ids);

    List<WithdrawalOrderDO> selectAllWithCustomer();
}
