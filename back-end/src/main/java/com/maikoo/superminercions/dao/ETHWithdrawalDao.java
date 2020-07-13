package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.ETHWithdrawalDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

import java.util.List;

@Mapper
public interface ETHWithdrawalDao {
    @Insert("INSERT " +
            "INTO " +
                "c_order_eth_withdrawal" +
                "(c_order_id, wallet_address, quantity) " +
            "VALUES" +
                "(#{id}, #{walletAddress}, #{quantity})")
    int insert(ETHWithdrawalDO ethWithdrawalDO);

    @Select("SELECT " +
                "coew.id " +
            "FROM " +
                "c_order co " +
                "JOIN c_order_eth_withdrawal coew ON co.id = coew.c_order_id " +
                "AND co.deleted_at IS NULL " +
            "WHERE " +
                "co.c_user_id = #{customerUserId}")
    List<Long> selectPageIds(long customerUserId);

    List<ETHWithdrawalDO> selectByIds(@Param("ids") List<Long> ids);

    ETHWithdrawalDO selectOne(long orderSN);

    List<ETHWithdrawalDO> selectAllWithCustomer();
}
